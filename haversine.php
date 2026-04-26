<?php
function haversineDistance($lat1, $lon1, $lat2, $lon2, $unit = 'K')
{
    // Convert degrees to radians
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    // Earth radius in km
    $earthRadius = 6371;

    // Differences
    $dlat = $lat2 - $lat1;
    $dlon = $lon2 - $lon1;

    // Haversine formula
    $a = sin($dlat / 2) * sin($dlat / 2) +
        cos($lat1) * cos($lat2) *
        sin($dlon / 2) * sin($dlon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;

    // Convert to other units if needed
    if ($unit == 'M') {
        $distance *= 0.621371; // Kilometers to miles
    } elseif ($unit == 'N') {
        $distance *= 0.539957; // Kilometers to nautical miles
    }

    return $distance;
}



function nearest_outlet(array $coords, $ignore = [])
{
    $conn = new mysqli("localhost", "root", "", "crud");

    $ignoreStr = implode(", ",$ignore);
    if (count($ignore)!=0) {
        $sql = "SELECT 
                i.id AS inventory_id,
                i.name AS name,
                i.location_id,
                l.name AS location_name,
                l.latitude,
                l.longitude
            FROM inventory i
            JOIN location l ON i.location_id = l.id
            WHERE i.id NOT IN ($ignoreStr)";
    } else {
        $sql = "SELECT 
                i.id AS inventory_id,
                i.name AS name,
                i.location_id,
                l.name AS location_name,
                l.latitude,
                l.longitude
            FROM inventory i
            JOIN location l ON i.location_id = l.id";
    }

    $result = $conn->query($sql);

    if ($result->num_rows == 0) {
        // TODO: Throw error rather than return integer
        return -1;
    }

    $curr_min = INF;
    $outlet = "";
    $outlet_id = -1;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $lat = $row["latitude"];
            $long = $row["longitude"];

            $dist = haversineDistance($lat, $long, $coords[0], $coords[1]);
            if ($dist < $curr_min) {
                $curr_min = $dist;
                $outlet = $row["name"];
                $outlet_id = $row["inventory_id"];
            }
        }
    }

    if ($outlet_id == -1) {
        throw new Error("Probably no inventory");
    }

    return $outlet_id;
}

// echo nearest_outlet([27.6574627,85.2819522]);
?>s