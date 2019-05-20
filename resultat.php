<?php



//Définition des paramètres par défaut
$LARGEUR_MESURE_VISEUR_mm = 4;
$HAUTEUR_MESURE_VISEUR_mm = 115;
$ORIGINE_X_MESURE_VISEUR_mm = 120;
$ORIGINE_Y_MESURE_VISEUR_mm = 50;

$TAILLE_TRAIT_PETIT_mm = 1;
$TAILLE_TRAIT_GROS_mm = 3;
$DECALAGE_TEXTE_X_mm = 2;
$DECALAGE_TEXTE_Y_mm = -0.5;
$DISTANCE_AFFICHAGE_MIN = 0;
$DISTANCE_AFFICHAGE_MAX = 70;
$POLICE_NUMERO = 4;
$TAILLE_TRAIT_mm = 0.2;
$POULIE=False;


$mesure = array(
  array( 10,  2.3 ),
  array( 15,  2.48 ),
  array( 20,  3.06 ),
  array( 25,  3.7 ),
  array( 30,  4.3 ),
  array( 35,  4.8 ),
  array( 50,  7.2 )
);

//Surcharge des paramètre si présents
if ( isset( $_REQUEST["tabMesure"] ) ) {
   $mesure = array();
   foreach( $_REQUEST["tabMesure"] as $distance => $reglage ){
      $mesure[] = array($distance, $reglage);
   }
}

if ( isset( $_REQUEST["largeur"] ) )
   $LARGEUR_MESURE_VISEUR_mm = $_REQUEST["largeur"];


if ( isset( $_REQUEST["hauteur"] ) )
   $HAUTEUR_MESURE_VISEUR_mm = $_REQUEST["hauteur"];


if ( isset( $_REQUEST["distMin"] ) )
   $DISTANCE_AFFICHAGE_MIN = $_REQUEST["distMin"];


if ( isset( $_REQUEST["distMax"] ) )
   $DISTANCE_AFFICHAGE_MAX = $_REQUEST["distMax"];


if ( isset( $_REQUEST["taillePolice"] ) )
   $POLICE_NUMERO = $_REQUEST["taillePolice"];

if ( isset( $_REQUEST["tailleTrait"] ) )
   $TAILLE_TRAIT_mm = $_REQUEST["tailleTrait"];


if ( isset( $_REQUEST["isPoulie"] ) &&  $_REQUEST["isPoulie"] == 1 )
   $POULIE = True;




//calcul des tailles de traits
$TAILLE_TRAIT_PETIT_mm  = $LARGEUR_MESURE_VISEUR_mm / 4.0;
$TAILLE_TRAIT_GROS_mm = 3 * $TAILLE_TRAIT_PETIT_mm ;
$DECALAGE_TEXTE_X_mm = $TAILLE_TRAIT_PETIT_mm  * 2;





//Traitement générique


require('PolynomialRegression/PolynomialRegression/PolynomialRegression.php');	

require('fpdf.php');

$pdf=new FPDF('P','mm','A4');
$pdf->Open();
$pdf->AddPage();

$pdf->SetFont('Arial','',10);

//affichage des mesures
$pdf->Text( 10 , 10, "Mesures utilisee pour le calcul" );
$cpt = 0;
foreach ( $mesure as $valeur ){
  $pdf->Text( 10 , 20 + 5 * $cpt, "Distance : " . strval($valeur[0]) . " - Reglage : " . strval($valeur[1]) );
  $cpt += 1;
}

//calcul coef polynomiaux
bcscale( 10 ); 

$polynomialRegression = new PolynomialRegression( 4 ); 
foreach ( $mesure as $dataPoint ){
   if( $dataPoint[ 0 ] != null && $dataPoint[ 1 ] != null ){
      $polynomialRegression->addData( $dataPoint[ 0 ], $dataPoint[ 1 ] );
   }
}	
$coefficients = $polynomialRegression->getCoefficients();


//affichage des coef

$pdf->Text( 100 , 10, "Coeficients regression polynomiale" );
$pdf->Text( 100 , 15, "Coef X3 = " . strval($coefficients[3]) );
$pdf->Text( 100 , 20, "Coef X2 = " . strval($coefficients[2]) );
$pdf->Text( 100 , 25, "Coed X  = " . strval($coefficients[1]) );
$pdf->Text( 100 , 30, "Coef 0  = " . strval($coefficients[0]) );


$pdf->Text( 100 , 45, "Equation  = " . strval($coefficients[3]). " x3 + " . strval($coefficients[2]). " x2 + " . strval($coefficients[1]). " x + " . strval($coefficients[0]) );


//Affichage texte divers

$pdf->Text( 10 , 250, "Imprimer en gardant les proportions d'origine" );


//Calsul des valeurs

$data = array();

//$data[0] = array( $ORIGINE_X_MESURE_VISEUR_mm, $ORIGINE_Y_MESURE_VISEUR_mm + 10 * ( $coefficients[0] ) );

for( $distance = $DISTANCE_AFFICHAGE_MIN ; $distance <= $DISTANCE_AFFICHAGE_MAX ; $distance++ ){
  $coordX = $ORIGINE_X_MESURE_VISEUR_mm;
  $coordY = $ORIGINE_Y_MESURE_VISEUR_mm + 10 * ( ( $coefficients[3] * ($distance * $distance * $distance) ) + ( $coefficients[2] * ($distance * $distance) ) + ($coefficients[1] * $distance) + $coefficients[0] );
  $data[$distance] = array( $coordX, $coordY );
}

$pdf->Text( 10 , 10, "Resultat Calcul" );
$cpt = 0;
for ( $distance = 10; $distance <= 70; $distance += 10 ){
  $pdf->Text( 10 , 100 + 5 * $cpt, "Distance : " . strval($distance) . " - Reglage : " . strval( $data[$distance][1] - $ORIGINE_Y_MESURE_VISEUR_mm ) );
  $cpt += 1;
}



//--------------------------------------------------
//remplacement de donne

//-------------------------------------------------


//dessin du cadre
$pdf->Line(
      $ORIGINE_X_MESURE_VISEUR_mm, 
      $ORIGINE_Y_MESURE_VISEUR_mm,
      $ORIGINE_X_MESURE_VISEUR_mm + $LARGEUR_MESURE_VISEUR_mm,
      $ORIGINE_Y_MESURE_VISEUR_mm );

$pdf->Line(
      $ORIGINE_X_MESURE_VISEUR_mm + $LARGEUR_MESURE_VISEUR_mm,
      $ORIGINE_Y_MESURE_VISEUR_mm,
      $ORIGINE_X_MESURE_VISEUR_mm + $LARGEUR_MESURE_VISEUR_mm,
      $ORIGINE_Y_MESURE_VISEUR_mm + $HAUTEUR_MESURE_VISEUR_mm );

$pdf->Line(
      $ORIGINE_X_MESURE_VISEUR_mm + $LARGEUR_MESURE_VISEUR_mm,
      $ORIGINE_Y_MESURE_VISEUR_mm + $HAUTEUR_MESURE_VISEUR_mm ,
      $ORIGINE_X_MESURE_VISEUR_mm,
      $ORIGINE_Y_MESURE_VISEUR_mm + $HAUTEUR_MESURE_VISEUR_mm );

$pdf->Line(
      $ORIGINE_X_MESURE_VISEUR_mm,
      $ORIGINE_Y_MESURE_VISEUR_mm + $HAUTEUR_MESURE_VISEUR_mm,
      $ORIGINE_X_MESURE_VISEUR_mm,
      $ORIGINE_Y_MESURE_VISEUR_mm );

$pdf->SetFont('Arial','',$POLICE_NUMERO);
$pdf->SetLineWidth($TAILLE_TRAIT_mm);
foreach ($data as $distance => $dataDist) {
   $coordX = $dataDist[0];
   $coordY = $dataDist[1];

   $distanceMarquage = 5;
   if( $POULIE == True ){
      $distanceMarquage = 10;
   }

   if( fmod( $distance , 5 ) == 0.0 ){
      $pdf->Line($coordX, $coordY, $coordX + $TAILLE_TRAIT_GROS_mm, $coordY);
   } else {
      $pdf->Line($coordX, $coordY, $coordX + $TAILLE_TRAIT_PETIT_mm, $coordY);
   }

   if( fmod( $distance , $distanceMarquage ) == 0.0 ){
      $pdf->Text( $coordX  + $DECALAGE_TEXTE_X_mm , $coordY + $DECALAGE_TEXTE_Y_mm, $distance );
   }

}

$pdf->Output();  

?>

