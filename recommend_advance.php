<?php
    function distance($x,$y,$a=1) {
        $sum = 0;
        if(count($x) != count($y)) {
            throw new Exception("Vectors must be of the same length");
        }
        for($i = 0;$i < count($x);$i++) {
            $sum += pow($x[$i] - $y[$i], 2);
        }
        return sqrt($sum)+1-$a;
    }

    function min_max_scaler($x) {
        $min = min($x);
        $max = max($x);
        return min_max_scaler_helper($x,$min,$max);
    }

    function min_max_scaler_helper($x, $min, $max) {
        $scaled = [];
        foreach($x as $value) {
            $scaled[] = ($value - $min) / ($max - $min);
        }
        return $scaled;
    }

    function one_hot_code_encoding($x) {
        $unique_values = array_unique($x);
        asort($unique_values);
        $unique_values = array_values($unique_values);
        return one_hot_code_encoding_helper($x,$unique_values);
    }

    function one_hot_code_encoding_helper($x,$unique_values) {
        $encoded = [];
        foreach($x as $value) {
            $row = array_fill(0, count($unique_values), 0);
            $index = array_search($value, $unique_values);
            if ($index !== false) {
                $row[$index] = 1;
            }
            $encoded[] = $row;
        }
        return $encoded;
    }

    function knn($curr_items_vectors, $item_vectors) {
        $distances = [];
        foreach($curr_items_vectors["prop_id"] as $curr_item_id => $curr_item_prop_id) {
            foreach($item_vectors["prop_id"] as $item_id=> $item_prop_id) {
                $item_vector = array_merge(
                    [$item_vectors["price"][$item_id]],
                    [$item_vectors["area"][$item_id]],
                    $item_vectors["bedroom"][$item_id],
                    $item_vectors["bathroom"][$item_id],
                    $item_vectors["location"][$item_id],
                );
                $curr_item_vector = array_merge(
                    [$curr_items_vectors["price"][$curr_item_id]],
                    [$curr_items_vectors["area"][$curr_item_id]],
                    $curr_items_vectors["bedroom"][$curr_item_id],
                    $curr_items_vectors["bathroom"][$curr_item_id],
                    $curr_items_vectors["location"][$curr_item_id],
                );


                $dist = distance($curr_item_vector, $item_vector,$curr_items_vectors["weights"][$curr_item_id]);
                
                if(array_key_exists($item_prop_id, $distances)) {
                    if($dist < $distances[$item_prop_id]) {
                        $distances[$item_prop_id] = $dist;
                    }
                }else { 
                    $distances[$item_prop_id] = $dist;
                }
            }
        }
        
        asort($distances);

        return array_keys(array_slice($distances,0,5,true));
    }

    function get_item_vectors($conn) {
        $sql = "SELECT prod_id,price,location,bedroom,area,bathroom FROM prop_detail";
        $result = $conn->query($sql);
        $item_vectors = [];

        if (!$result) {
            die("Query failed: " . $conn->error);
        }
        
        $vectors = [
            'prop_id_vector'=>[],
            'price_vector'=>[],
            'location_vector'=>[],
            'bedroom_vector'=>[],
            'area_vector'=>[],
            'bathroom_vector'=>[]
        ];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                array_push($vectors["prop_id_vector"],$row['prod_id']);
                array_push($vectors["price_vector"],$row['price']);
                array_push($vectors["location_vector"],$row['location']);
                array_push($vectors["bedroom_vector"],$row['bedroom']);
                array_push($vectors["area_vector"],$row['area']);
                array_push($vectors["bathroom_vector"],$row['bathroom']);
            }
        }

        $item_vectors["prop_id"] = $vectors["prop_id_vector"];
        $item_vectors["price"] = min_max_scaler($vectors["price_vector"]);
        $item_vectors["location"] = one_hot_code_encoding($vectors["location_vector"]);
        $item_vectors["bedroom"] = one_hot_code_encoding($vectors["bedroom_vector"]);
        $item_vectors["area"] = min_max_scaler($vectors["area_vector"]);
        $item_vectors["bathroom"] = one_hot_code_encoding($vectors["bathroom_vector"]);

        return $item_vectors;
    }

    function vectorize_curr_item($conn,$vectors) {
        $sql = "SELECT 
            MIN(CAST(price AS UNSIGNED))  AS min_price,
            MAX(CAST(price AS UNSIGNED))  AS max_price,
            MIN(area)   AS min_area,
            MAX(area)   AS max_area,
            GROUP_CONCAT(DISTINCT location) AS unique_locations,
            GROUP_CONCAT(DISTINCT bedroom)  AS unique_bedrooms,
            GROUP_CONCAT(DISTINCT bathroom) AS unique_bathrooms
        FROM prop_detail;
        ";
        $result = $conn->query($sql);

        if( !$result) {
            die("Query failed: " . $conn->error);
        }
        $row = $result->fetch_assoc();
        $item_vectors = [];
        $item_vectors["prop_id"] = $vectors["prop_id_vector"];

        $unique_locations = explode(",",$row["unique_locations"]);
        $unique_bedrooms = explode(",",$row["unique_bedrooms"]);
        $unique_bathrooms = explode(",",$row["unique_bathrooms"]);

        asort($unique_locations);
        asort($unique_bedrooms);
        asort($unique_bathrooms);

        $unique_locations = array_values($unique_locations);
        $unique_bedrooms = array_values($unique_bedrooms);
        $unique_bathrooms = array_values($unique_bathrooms);



        $item_vectors["price"] = min_max_scaler_helper($vectors["price_vector"],$row["min_price"],$row["max_price"]);
        $item_vectors["location"] = one_hot_code_encoding_helper($vectors["location_vector"],$unique_locations);
        $item_vectors["bedroom"] = one_hot_code_encoding_helper($vectors["bedroom_vector"],$unique_bedrooms);
        $item_vectors["area"] = min_max_scaler_helper($vectors["area_vector"],$row["min_area"],$row["max_area"]);
        $item_vectors["bathroom"] = one_hot_code_encoding_helper($vectors["bathroom_vector"],$unique_bathrooms);
        $item_vectors["weights"] = $vectors["weights"];
        return $item_vectors;
    }
    function recommend($curr_items_vectors)   {
        include 'config.php';
        $item_vectors = get_item_vectors($conn);



        $recommended_items = knn(vectorize_curr_item($conn,$curr_items_vectors), $item_vectors);
        return $recommended_items;
    }


    function get_recommended_items_for_curr_user() {
        include 'config.php';
        session_start();
        $sql = "select * from interaction_log il join prop_detail pd on il.prop_id = pd.prod_id where uid = ?";
        $curr_items_vectors = [
            'prop_id_vector'=>[],
            'price_vector'=>[],
            'location_vector'=>[],
            'bedroom_vector'=>[],
            'area_vector'=>[],
            'bathroom_vector'=>[],
            'weights'=>[]
        ];

        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result) {
            die("Query failed: " . $conn->error);
        }

        while($row=$result->fetch_assoc()) {
            array_push($curr_items_vectors["prop_id_vector"],$row['prod_id']);
            array_push($curr_items_vectors["price_vector"],$row['price']);
            array_push($curr_items_vectors["location_vector"],$row['location']);
            array_push($curr_items_vectors["bedroom_vector"],$row['bedroom']);
            array_push($curr_items_vectors["area_vector"],$row['area']);
            array_push($curr_items_vectors["bathroom_vector"],$row['bathroom']);
            array_push($curr_items_vectors["weights"],$row['weight']);

        }
        return recommend($curr_items_vectors);
    }

    function get_recommended_items_for_curr_product($id) {
        include 'config.php';
        $sql = "select * from prop_detail where prod_id = ?";
        if(!isset($id)) {
            return [];
        }
        $stmt = $conn->prepare($sql);   
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows == 0) {
            return [];
        }
        $row = $result->fetch_assoc();

        $curr_items_vectors = [
            'prop_id_vector'=>[$row['prod_id']],
            'price_vector'=>[$row['price']],
            'location_vector'=>[$row['location']],
            'bedroom_vector'=>[$row['bedroom']],
            'area_vector'=>[$row['area']],
            'bathroom_vector'=>[$row['bathroom']]
        ];



        if (!$result) {
            die("Query failed: " . $conn->error);
        }
        return recommend($curr_items_vectors);
    }

?>