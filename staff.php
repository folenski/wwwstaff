 <?php
require_once ("includes/application_top.php") ;
require ("includes/bs3_header.php"); ?>


<!-- !PAGE CONTENT! -->

<!-- Header -->
<header id="portfolio" class="w3-theme">

<div class="w3-display-container w3-padding-64">

  <div class="w3-display-left  w3-padding">
      <img src="images/staff_logo2.png"  width="70px"  >
  </div>
  <div class="w3-display-left"  style="margin-left:100px;" >
      <span class="w3-text-black w3-opacity  w3-xxxlarge"  style="text-shadow:1px 1px 0 #444" ><b>Staff-kiev</b></span>.com 
  </div>
  <div class="w3-display-right w3-padding "><i class="tiny material-icons">phonelink_ring</i>  0932901228 - 0673355979</div> 
</div>
 </header>
    
 <nav id="navbar" class="w3-bar w3-theme-d5 w3-card ">
      <span id="navbar_logo" class="w3-bar-item ">  </span> 
      <!-- function print_menu_xml_one (object $xml, string $lang, string $tagstart, string $tagend,  string $active, string $notactive ): string { -->
      <?= print_menu_xml_one  ($data, $sitecnf->langue, '<a class="w3-bar-item w3-button w3-mobile w3-large w3-theme-l3 ', '</a>', 'w3-black', ''  ) ; ?> 
      <span id="navbar_logo2" class="w3-bar-item w3-mobile "> xxx </span> 
  </nav>

<!-- Message -->
<?php if (isset ($message)): ?>
  <div class="w3-panel <?= $message[0]; ?> w3-display-container">
  <span onclick="this.parentElement.style.display='none'"
  class="w3-button w3-red w3-large w3-display-topright">x</span>
  <p> <?= $message[1]; ?> </p>
</div> 
<?php endif ?>

<!-- les pages -->
<div  class="teal lighten-5" >   
  <?php do { ?>        
    <div id="<?=$site_page_act->id;?>" class="card-panel z-depth-5"  >
    <h1><b><?=$site_page_act->titre;?></b></h1>
    <?= str_replace ("<!--url-->", $sitecnf->url, $site_page_act->contenu  ); ?>
    </div>
  <?php } while ($site_page_act = page_courante ($data, (int)$site_page_act->id + 1, $sitecnf->langue) ) ; ?>

  </div>
  <!-- Footer -->
<?php require ("includes/bs3_footer.php"); ?>