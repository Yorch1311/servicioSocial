<?php
require("generateFunctions.php");

//Algoritmo de ranking de usuarios

//en la BD se hara un select por cada usuario, pero aqui no.
$names = ["Pancho","Juan","Ana","Alex","Karla","Kevin","Jhon","Paco","Alexis","Carlos","Kim","Chris","Leo","Sol","Noel"];//select * in user

//Si se crean muchos usuarios y pocos videos entonces es más probable que haya usuarios sin videos entonces serán sin Rango

/*
    
    createUsers retorna una matriz 
*/
$lista = createUsers($names, 100, 150);
$userVideos = createVideos($lista, 100, 100, 2000, 50);
$userEval = createVideos($lista, 80, 100, 1500, 80);

//comprobar si usuarios se repiten. cuando ocurre genera errores. esto no importara cuando exista una BD de verdad.
for($i=0; $i<count($lista); $i++){
    for($j=$i+1; $j<count($lista); $j++){
        if($lista[$i] == $lista[$j]){
            echo "el usuario ".$lista[$i]." se repite<br>";
        }
    }
}

//las solicitudes a la BD se hacen antes de este punto
$p = 0.6;

rankUsers($lista, $userVideos, $userEval, $p, 13);

/**
 * Ordena y devuelve una cantidad de usuarios para que evaluen.
 * 
 * @param array $users lista de usuarios.
 * @param array $userVideos lista de videos de los usuarios con sus datos.
 * @param array $userEval lista de videos que los usuarios han evaluado.
 * @param double $p valor entre 0 y 1.
 * @param int $cantUser cantidad de usuarios que quiere recibir de retorno.
 * @return array arreglo con los usuarios que validadran el video.
 */
function rankUsers($users, $userVideos, $userEval, $p, $cantUser){
    for($user=0; $user < count($users); $user++){

        $id = $users[$user];
        $name = $id["name"];

        $videoEvalS = evaluar($id, $userVideos);
        $videoEvalV = evaluar($id, $userEval);
    
        /*echo "----------".$id["name"]."----------<br>";
        echo "Valor de S = ".$videoEvalS."<br>";   
        echo "Valor de V = ".$videoEvalV."<br>";*/

        $q = 1 - $p;

        // formula 3: cu = s*p + v*q
        $cu[$name]["cu"] = ($videoEvalS*$p) + ($videoEvalV*$q);
        $cu[$name]["vistas"] = $id["userView"];
        $cu[$name]["id"] = $name;

        //echo "Valor de CU = ".$cu[$name]["cu"]."<br>";
    }

    rsort($cu);//funcion que ordena de mayor a menor
    //echo "<br>";
    $i = 0;
    //imprimimos el valor CU de los usuarios, el cual ya esta ordenado de mayor a menor
    foreach($cu as $value){
        echo "----------".$value["id"]."----------<br>";
        echo "Valor de CU = ".$value["cu"]."---------------------------------".$i++."<br>";
    }

    //rankings
    echo "----Rangos----<br>";
    $totUser = count($users);

    /* 
        se crean 2 listas, la $noRank la cual tiene los usuario que su valor CU es 0 pero que si han visto videos.
        y los que tienen un CU != 0 se asignan a la lista $rank para proceder a asignarle un rango especifico.
    */
    for($i = 0; $i < $totUser; $i++){

        if($cu[$i]["cu"] == 0 && $cu[$i]["vistas"] > 0){
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

    //Recorriendo las listas que tienen a los usuarios rankeados
    echo "----Sin Rango----<br>";
    if(count($noRank) > 0){
        foreach($noRank as $value){
            echo "$value <br>";
        }
    }
    echo "----Rango Medio-Bajo----<br>";
    if(count($midLowRank) > 0){
        foreach($midLowRank as $value){
            echo "$value <br>";
        }
    }
    echo "----Rango Alto----<br>";
    if(count($highRank) > 0){
        foreach($highRank as $value){
            echo "$value <br>";
        }
    }

    //seccion de seleccion de usuarios por su ranking
    $totUser = count($users);
    //cantidad de usuarios a escoguer por rango en base al parametro recividó. Alto 40%, Medio-Bajo 40%, sin rango 20%
    //si pide 3, entonces seran 1 altos 1 medio-bajo y 1 sin rango
    // 3*0.4 = 1.2 (se redondea a 1)      3*0.2= 0.6 (redondea a 1)
    
    //cantidad de usaurios que evaluaran por rango 
    $alto = round($cantUser * 0.4);
    $medioBajo = round($cantUser * 0.4);
    $sinRango = round($cantUser * 0.2);

    //llamar a la funcion que regresa un arreglo aleatorio con las cantidades que le envie
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

/**
 * Retorna X cantidad de datos dados por el usuario
 * 
 * @param array $arreglo arreglo donde se encuentran los datos que el usuario quiere randimizar.
 * @param int $tam tamaño del arreglo que devolvera la funcion.
 * @return array arreglo con tamaño X que incluyen datos aleatorios del $arreglo.
 */
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

/**
 * Evalua a los usuarios en base a unas formulas.
 * 
 * Formula para la evaluacion sin penalizacion
 * formula 1: Si = Sv / (S+ - S-) 
 * Se promedian las evaluaciones sin penalizacion de todos los videos subidos por el usuario
 * formula 2: S = sumatoria de $videoEval / n
 * 
 * @param string $user nombre de usuario.
 * @param array $userVideos lista de videos que el usuario ha subido o a evaluado.
 * @param double valor de evaluacion.
 */
function evaluar($user, $userVideos){
    $name = $user["name"];
    $videoEval[$name]["eval"] = 0.0;
    $videoEval[$name]["videos"] = 0.0;// cantidad de videos
    $videoEval[$name]["videosDown"] = 0.0;// cantidad de videos dados de baja

    //echo "$users[$user]\n";
    for($video = 0; $video < count($userVideos); $video++){
        if($user["name"] == $userVideos[$video]["name"]){
            
            /* 
                Formula para la evaluacion sin penalizacion
                formula 1: Si = Sv / (S+ - S-) 
            */
            
            $videoEval[$name]["eval"] += $userVideos[$video]["view"] / ($userVideos[$video]["star+"] - $userVideos[$video]["star-"]);
            $videoEval[$name]["videos"]++;

            if($userVideos[$video]["status"] == "B"){
                $videoEval[$name]["videosDown"]++;
            }
            //echo $userVideos[$video][0];
        }
    }
    /*
    Se promedian las evaluaciones sin penalizacion de todos los videos subidos por el usuario
    formula 2: S = sumatoria de $videoEval / n
    */
    if($videoEval[$name]["eval"] != 0){
        $videoEval[$name]["eval"] = $videoEval[$name]["eval"] / $videoEval[$name]["videos"];
    }

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
    while($i < $videoEval[$name]["videosDown"]){
        $videoEval[$name]["eval"] -= ($videoEval[$name]["eval"] * $Sp);
        $i++;
    }

    return $videoEval[$name]["eval"];
}

?>