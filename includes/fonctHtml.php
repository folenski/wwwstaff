<?php 
/*  
    Collection de foncitons pour générer du html
    le 11/11/2020  - v1.0 - mfe
*/

function appPageNotFound(string $message, bool $type=true): string {
// retourne un meesage d'erreur
  return   '<div class="jumbotron shadow "><h1 class="display-4 text-center text-danger">'  
         . ( ($type) ? '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>' : '<i class="fa fa-plus-square" aria-hidden="true"></i>' )
         .  "&nbsp;$message</h1></div>";
}


function appTopCssScript(string $framework, bool $css=false) : string {
  // retourne le framework  css ou script  et si on doit le prendre en local 
  // frameWork : Materialize-1, jquery-3.51, animate, bootstrap-4
    $ret = "";
    $fw = explode ("|", $framework);
    foreach ($fw as $fwvalue) {
      switch (strtolower($fwvalue)) {
        case "materialize-1": 
          if (! FW_LOCAL) {
            if ($css) {
              $ret .= '<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">';
              $ret .= '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">';
            }
            else 
              $ret .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>';
          } 
          else {
            if ($css) {
              $ret .= '<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">';
              $ret .= '<link rel="stylesheet" href="' . REP_CSS . 'materialize.min.css">';
            }
            else 
              $ret .= '<script type="text/javascript" src="' . REP_JS . 'materialize.min.js"></script>';
          }
          break;
        case "jquery-3.51": 
            if (! FW_LOCAL) {
              if ($css)  $ret .= "";
              else       $ret .= '<script src="http://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>';
            } 
            else {
              if ($css) $ret .= "";
              else      $ret .= '<script type="text/javascript" src="' . REP_JS . 'jquery.min.js"></script>';
            }
            break;
        case "animate": 
          if (! FW_LOCAL) {
            if ($css)  $ret .= '<link rel="stylesheet"  href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>';
            else       $ret .= "";
          } 
          else {
            if ($css) $ret .= '<link rel="stylesheet" href="' . REP_CSS . 'animate.min.css">';
            else      $ret .= "";
          }
          break;
          case "bootstrap-4":   // pas de mode local
            if (! FW_LOCAL) {
              if ($css) {
                $ret .= '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">';
                $ret .= '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">';
              }
              else 
                $ret .= '<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>';
            } 
            else {
              if ($css) {
                $ret .= '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">';
                $ret .= '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">';
              }
              else 
                $ret .= '<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>';
          }
            break;
        default :
          $ret .="";
      }
    }
  
    return $ret;
  }