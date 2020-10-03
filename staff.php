 <?php
 // site php 
 // les datas sont stockées dans le repertoire xmldata
require_once ("includes/application_top.php") ;
require ("includes/bs3_header.php"); 
?>

<!-- Header -->
<header  >

<div class="navbar-fixed " >
  <nav class="<?=$sitecnf->theme;?> darken-1  animate__animated animate__zoomIn animate__faster" >
    <div class="nav" >
      <a href="#" class="brand-logo black-text w3-margin-left"><img src="images/logo.png"></a>
      <a href="#" data-target="mobile-demo" class="sidenav-trigger"><i class="material-icons">menu</i></a>
      <ul class="right hide-on-med-and-down" > <?= print_menu_xml_one  ($data, $sitecnf->langue, '<li> <a', '</a></li>', 'active', '') ; ?>  </ul>
    </div>
  </nav>
</div>

<ul class="sidenav" id="mobile-demo">  <?= print_menu_xml_one  ($data, $sitecnf->langue, '<li> <a', '</a></li>', 'active', '') ; ?>   </ul>
</header>




<!-- les pages -->
<div class="w3-padding  <?=$sitecnf->theme;?>  lighten-5" >   

<!-- Message -->
  <?php if (isset ($message)): ?>
    <div class="w3-panel <?= $message[0]; ?> w3-display-container">
    <span onclick="this.parentElement.style.display='none'"
    class="w3-button w3-red w3-large w3-display-topright">x</span>
    <p> <?= $message[1]; ?> </p>
  </div> 
  <?php endif ?>

  <?php do { ?>        
    <div class="card-panel w3-margin  z-depth-5 animate__animated animate__zoomIn animate__faster " >
    <div id="<?=$site_page_act->id;?>" class="scrollspy"  >
    <h1><b><?=$site_page_act->titre;?></b></h1>
    <?= str_replace ("<!--url-->", $sitecnf->url, $site_page_act->contenu  ); ?>
    </div> </div>
  <?php } while ($site_page_act = page_courante ($data, (int)$site_page_act->id + 1, $sitecnf->langue) ) ; ?>
</div>

<div class="fixed-action-btn"  style="right: 100px;" >
  <a class="tooltipped btn-floating btn-large red pulse" data-position="top" data-tooltip="тел. 0932901228<br>тел. 0673355979 "><i class="large material-icons">phone</i></a>
</div>

<!-- fleche -->
<div class="fixed-action-btn">
<a href="#" data-scroll id="back-top" class="btn-floating btn-large black ">
  <i class="large material-icons">arrow_upward</i></a>
</div>


  </div> 
  <!-- Footer -->
<?php require ("includes/bs3_footer.php"); ?>