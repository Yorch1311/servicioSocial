<?php
//Crea una lista de nombres de usuario
function createUsers($names, $total, $userViews){
    /**
     * Crea un arreglo de nombres de usuarios aleatorios.
     * 
     * @param array $names lista de nombres de usuarios,
     * @param int $total cantidad de usuarios que se generaran.
     * @param int $userViews cantidad maxima de videos que el usuario ha visto.
     * @return array
     */

    for($i = 0; $i < $total; $i++){
        $posN = rand(0, count($names)-1);
        $num = rand(0, 10000);
        $userView = rand(0, $userViews);
        $newUsers[$i]["name"] = $names[$posN]."_".$num;
        $newUsers[$i]["userView"] = $userView;
    }
    return $newUsers;
}

function createVideos($users, $total, $maxStars, $maxViews){

    for($i = 0; $i < $total; $i++){

        $posStar = rand(0, $maxStars);
        $negStars = rand(0, $maxStars);
        //no pueden ser iguales porque sino genera error por dividir entre 0
        while( $posStar == $negStars){
            $posStar = rand(1, $maxStars);
            $negStars = rand(0, $maxStars);
        }

        $videoViews = rand(0, $maxViews) + $posStar; + $negStars;

        $owner = rand(0, count($users)-1);

        //la probabilidad de que un video este eliminado sera de 1/4
        $num = rand(1, 4);
        if( $num == 1){
            $status = "B";
        }else{
            $status = "A";
        }
        

        $newVideo[$i]["name"] = $users[$owner]["name"];
        $newVideo[$i]["view"] = $videoViews;
        $newVideo[$i]["star+"] = $posStar;
        $newVideo[$i]["star-"] = $negStars;
        $newVideo[$i]["status"] = $status;
    }
    
    return $newVideo;
}
?>