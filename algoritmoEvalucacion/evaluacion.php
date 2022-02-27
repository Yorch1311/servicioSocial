<?php
//Algoritmo de renking de usuarios

/*Variables consideradas para cada video subido por el usuario
    S+ = estrellas positivas
    S- = estrellas negativas
    Sv = reproducciones del video
*/
$users = ["Pancho","Juan","Ana","Alex","Karla","Kevin","Jhon","Paco","Alexis","Carlos","NewUser1","NewUser2","NewUser3"];//select * in user
//en la BD se hara un select por cada usuario, pero aqui no.
$userVideos = [
    ["Pancho",100,5,2,"A"],
    ["Juan",50,15,2,0,"A"],
    ["Pancho",80,20,12,"A"],
    ["Alex",29,13,8,"A"],
    ["Karla",102,12,20,"B"],
    ["Kevin",150,50,10,"A"],
    ["Jhon",110,20,10,"A"],
    ["Paco",68,10,12,"A"],
    ["Alexis",290,30,8,"A"],
    ["Carlos",95,23,2,"B"],
];

$userEval = [
    ["Pancho",50,5,2,"A"],
    ["Juan",500,15,2,0,"A"],
    ["Ana",50,20,12,"A"],
    ["Alex",290,13,8,"A"],
    ["Karla",202,12,20,"B"],
    ["Kevin",53,12,2,"A"],
    ["Jhon",200,25,5,"A"],
    ["Paco",180,40,20,"A"],
    ["Alexis",75,10,12,"A"],
    ["Carlos",20,5,2,"B"],
];
//las solicitudes a la BD se hacen antes de este punto
$p = 0.6;
rankUsers($users, $userVideos, $userEval, $p, 7);

function rankUsers($users, $userVideos, $userEval, $p, $cantUser){
    for($user=0; $user < count($users); $user++){

        $id = $users[$user];

        $videoEvalS = evaluar($id, $userVideos);
        $videoEvalV = evaluar($id, $userEval);
    
        echo "----------".$id."----------<br>";
        echo "Valor de S = ".$videoEvalS."<br>";   
        echo "Valor de V = ".$videoEvalV."<br>";

        $q = 1 - $p;
        // formula 3: cu = s*p + v*q
        $cu[$id]["cu"] = ($videoEvalS*$p) + ($videoEvalV*$q);
        $cu[$id]["id"] = $id;
        echo "Valor de CU = ".$cu[$id]["cu"]."<br>";
    }

    rsort($cu);//mayor a menor
    echo "<br>";
    foreach($cu as $value){
        echo "----------".$value["id"]."----------<br>";
        echo "Valor de CU = ".$value["cu"]."<br>";
    }

    //rankings
    echo "----Rangos----<br>";
    $totUser = count($users);
    for($i = 0; $i < $totUser; $i++){

        if($cu[$i]["cu"] == 0){//rango alto
            $noRank[] = $cu[$i]["id"];
        }else{
            $rank[] = $cu[$i]["id"];
        }
    }
    $totUser = count($rank);

    $alto = round($totUser * 0.5);// de 0 a X pos en el arreglo
    $medio = round($totUser * 0.5) + $alto;// de X a X+Y en el arreglo
    //$sinRango = round($users * 0.2) + $midLowRank;
    for($i = 0; $i < $totUser; $i++){

        if($i < $alto){//sin rango
            $highRank[] = $rank[$i];
        }else if($i < $medio){// rangos medio-bajo
            $midLowRank[] = $rank[$i];
        }
    }
    echo "----Sin Rango----<br>";
    foreach($noRank as $value){
        echo "$value <br>";
    }
    echo "----Rango Medio-Bajo----<br>";
    foreach($midLowRank as $value){
        echo "$value <br>";
    }
    echo "----Rango Alto----<br>";
    foreach($highRank as $value){
        echo "$value <br>";
    }

    //seccion de seleccion de usuarios por su ranking
    $totUser = count($users);
    //cantidad de usuarios a escoguer por rango en base al parametro recividó. Alto 40%, Medio-Bajo 40%, sin rango 20%
    //si pide 3, entonces seran 1 altos 1 medio-bajo y 1 sin rango
    // 3*0.4 = 1.2 (se redondea a 1)      3*0.2= 0.6 (redondea a 1)
    $alto = round($cantUser * 0.4);
    $medioBajo = round($cantUser * 0.4);
    $sinRango = round($cantUser * 0.2);

    //llamar a la funcion que regresa un arreglo aleatorio con los datos que le envie

    $testers = arregloAleatorio($highRank, $alto);
    //llamo a la misma funcion pero ahora concateno 2 arreglos, el de la linea anterior y el que recibo de la funcion
    $testers = array_merge($testers, arregloAleatorio($midLowRank, $medioBajo));
    $testers = array_merge($testers, arregloAleatorio($noRank, $sinRango));
    
    echo "<br>--------Usuarios escogidos--------<br>";
    foreach($testers as $id){
        echo $id."<br>";
    }
    //var_dump($testers);
}

function arregloAleatorio($arreglo, $tam){

    $newArray = [];

    if($tam > count($arreglo)){
        $newArray = $arreglo;
    }else{
        $i = 0;
        while($i < $tam){
            $ran = rand(0,count($arreglo)-1);
            $cont = 0;
            for($aPos = 0; $aPos < count($newArray); $aPos++){
                if($arreglo[$ran] == $newArray[$aPos]){
                    break;
                }
                $cont++;
            }
            if($cont == count($newArray)){
                $newArray[] = $arreglo[$ran];
                $i++;
            }
        }
    }
    return $newArray;
}

function evaluar($user, $userVideos){
    $videoEval[$user]["eval"] = 0.0;
    $videoEval[$user]["videos"] = 0.0;// cantidad de videos
    $videoEval[$user]["videosDown"] = 0.0;// cantidad de videos dados de baja

    //echo "$users[$user]\n";
    for($video = 0; $video < count($userVideos); $video++){
        if($user == $userVideos[$video][0]){

            /* 
            Formula para la evaluacion sin penalizacion
            formula 1: Si = Sv / (S+ - S-) 
            */
            
            $videoEval[$user]["eval"] += $userVideos[$video][1] / ($userVideos[$video][2] - $userVideos[$video][3]);
            $videoEval[$user]["videos"]++;

            if($userVideos[$video][4] == "B"){
                $videoEval[$user]["videosDown"]++;
            }
            //echo $userVideos[$video][0];
        }
    }
    /*
    Se promedian las evaluaciones sin penalizacion de todos los videos subidos por el usuario
    formula 2: S = sumatoria de $videoEval / n
    */
    if($videoEval[$user]["eval"] != 0){
        $videoEval[$user]["eval"] = $videoEval[$user]["eval"] / $videoEval[$user]["videos"];
    }
    //echo $videoEval[$user][0]."    ".$videoEval[$user][1]."<br>";

    /*
        Algoritmo de penalizacion
        i=0
        Sp = valor de penalizacion (la ingresa el admin)
        Sb = total de videos dados de baja

        𝑚𝑖𝑒𝑛𝑡𝑟𝑎𝑠 𝑖 < 𝑠𝑏 ℎ𝑎𝑐𝑒𝑟
        𝑠 = 𝑠 − (𝑠 ∗ 𝑠𝑝)
        𝑖 = 𝑖 + 1
    */
    $Sp = 0.5;
    $i = 0;
    while($i < $videoEval[$user]["videosDown"]){
        $videoEval[$user]["eval"] -= ($videoEval[$user]["eval"] * $Sp);
        $i++;
    }

    return $videoEval[$user]["eval"];
}

?>