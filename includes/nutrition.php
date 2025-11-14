<?php
// includes/nutrition.php — MySQLi version (no PDO)

// PHP 7 polyfill for str_contains
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && strpos($haystack, $needle) !== false;
    }
}

/**
 * Try to read daily target from user_health_profile if it exists,
 * otherwise fall back to 2200.
 */
function getDailyTarget(mysqli $conn, int $userId): int {
    $target = 2200;
    if ($stmt = $conn->prepare("SELECT daily_calorie_target FROM user_health_profile WHERE user_id = ? LIMIT 1")) {
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            if ($res && (int)$res['daily_calorie_target'] > 0) {
                $target = (int)$res['daily_calorie_target'];
            }
        }
        $stmt->close();
    }
    return $target;
}

/**
 * Remaining calories today = daily target - calories eaten today.
 * Calories eaten are computed from orders + order_items joined to menu.calories.
 */
function remainingCaloriesToday_mysqli(mysqli $conn, int $userId): array {
    $target = getDailyTarget($conn, $userId);

    $sql = "SELECT COALESCE(SUM(m.calories * oi.qty), 0) AS eaten
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN menu m ON m.id = oi.dish_id
            WHERE o.user_id = ? AND DATE(o.order_date) = CURDATE()";

    $eaten = 0;
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            $row = $stmt->get_result()->fetch_assoc();
            if ($row) $eaten = (int)$row['eaten'];
        }
        $stmt->close();
    }

    $remaining = max(0, $target - $eaten);
    return ['target' => $target, 'eaten' => $eaten, 'remaining' => $remaining];
}

function containsAny(string $haystack, array $needles): bool {
    $h = strtolower($haystack);
    foreach ($needles as $n) {
        if ($n !== '' && strpos($h, strtolower($n)) !== false) return true;
    }
    return false;
}

/**
 * Suggest dishes from menu using your session profile and calories window.
 * Uses only fields that exist in your `menu` table.
 */
function suggestDishes_mysqli(mysqli $conn, int $userId, string $mealType = 'lunch', int $limit = 10): array {
    $profile = $_SESSION['user_profile'] ?? ['allergies' => [], 'health' => [], 'fitness_goals' => []];

    $target = getDailyTarget($conn, $userId);
    $shares = ['breakfast'=>0.25,'lunch'=>0.35,'snacks'=>0.15,'dinner'=>0.25];
    $share = $shares[$mealType] ?? 0.3;
    $ideal = (int)($target * $share);
    $low   = max(100, $ideal - 200);
    $high  = $ideal + 200;

    $sql = "SELECT id,name,price,category,calories,base_ingredients,suitable_for,rating,dish_image
            FROM menu
            WHERE calories IS NOT NULL AND calories > 0
            LIMIT 200";

    $res = $conn->query($sql);
    $out = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $score = 50;
            $warnings = [];

            $ingredients  = (string)$row['base_ingredients'];
            $suitable_for = (string)$row['suitable_for'];
            $cals = (int)$row['calories'];

            // Allergy blocklist
            $allergyMap = [
                'dairy'    => ['milk','cheese','butter','curd','yogurt','paneer','cream','ghee'],
                'peanuts'  => ['peanut','groundnut','nut'],
                'gluten'   => ['wheat','maida','bread','atta'],
                'shellfish'=> ['shrimp','prawn','crab','lobster']
            ];
            $blocked = false;
            foreach (($profile['allergies'] ?? []) as $a) {
                $a = strtolower($a);
                if (isset($allergyMap[$a]) && containsAny($ingredients, $allergyMap[$a])) {
                    $blocked = true;
                    $warnings[] = "Allergy risk: $a";
                }
            }
            if ($blocked) continue;

            // Health-condition nudges (use suitable_for when present)
            if (in_array('bp', $profile['health'])) {
                if (containsAny($ingredients, ['salt','pickle','papad'])) $score -= 4;
                if (containsAny($suitable_for, ['high bp'])) $score += 6;
            }
            if (in_array('heart', $profile['health'])) {
                if (containsAny($ingredients, ['ghee','butter','oil','fried'])) $score -= 6;
                if (containsAny($suitable_for, ['heart'])) $score += 6;
            }
            if (in_array('diabetes', $profile['health'])) {
                if (containsAny($ingredients, ['sugar','jaggery','honey','sweet'])) $score -= 10;
                if (containsAny($suitable_for, ['diabetes'])) $score += 8;
            }

            // Calorie fit
            if ($cals >= $low && $cals <= $high) $score += 10;
            elseif ($cals > $high) $score -= 8;
            else $score -= 4;

            // Weight-loss preference
            if (in_array('weight loss', $profile['fitness_goals'])) {
                if ($cals <= $ideal) $score += 3; else $score -= 3;
            }

            // Bump with rating if present
            $rating = is_numeric($row['rating'] ?? null) ? (float)$row['rating'] : 0;
            $score += (int)round($rating);

            $out[] = ['dish' => $row, 'score' => $score, 'warnings' => $warnings];
        }
        $res->free();
    }

    usort($out, fn($a,$b) => $b['score'] <=> $a['score']);
    return array_slice($out, 0, $limit);
}
