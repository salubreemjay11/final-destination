<?php
// shared/event-types.php
function getEventTypesForRole($role, $pdo) {
    // Your existing function code here
    try {
        $query = "SELECT type_key, type_name, icon, visible_to FROM event_types WHERE is_active = 1 ORDER BY type_name";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $all_event_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $filtered_types = [];
        
        foreach ($all_event_types as $type) {
            if ($type['visible_to'] === null) {
                $filtered_types[] = [
                    'type_key' => $type['type_key'],
                    'type_name' => $type['type_name'],
                    'icon' => $type['icon']
                ];
                continue;
            }
            
            $visible_to = json_decode($type['visible_to'], true);
            
            if ($visible_to === null || empty($visible_to)) {
                $filtered_types[] = [
                    'type_key' => $type['type_key'],
                    'type_name' => $type['type_name'],
                    'icon' => $type['icon']
                ];
                continue;
            }
            
            if (in_array($role, $visible_to)) {
                $filtered_types[] = [
                    'type_key' => $type['type_key'],
                    'type_name' => $type['type_name'],
                    'icon' => $type['icon']
                ];
            }
        }
        
        if (empty($filtered_types)) {
            return [
                ['type_key' => 'home_visit', 'type_name' => 'Home Visit', 'icon' => '🏠']
            ];
        }
        
        return $filtered_types;
        
    } catch (Exception $e) {
        error_log("Error getting event types: " . $e->getMessage());
        return [
            ['type_key' => 'home_visit', 'type_name' => 'Home Visit', 'icon' => '🏠']
        ];
    }
}
?>