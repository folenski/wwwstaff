<?php 
require_once ("includes/application_top.php") ;
require ("includes/bs3_header.php") ;  // include server parameters  
?>
<body>
      <!-- The justified navigation menu is meant for single line per list item.
      Multiple lines will require custom code not provided by Bootstrap. -->
      <div class="container-fluid" >
          <!-- <?= $sitecnf->header ?> -->
          <img src="<?=$sitecnf->url;?>assets/img/header_staff.jpg" width="100%" height="200" >
      </div>
      <nav class="navbar nav-justified" data-spy="affix" data-offset-top="200" >
        <div class="navbar-header">
            <a class="navbar-brand" href="#">14.22.6.66.44  & 44.22.66.44 </a>
         </div>
        <ul class="nav nav-justified" >
        <!-- <ul class="nav navbar-nav"  data-spy="affix" data-offset-top="100"   > -->
      <!-- <ul class="nav nav-justified sticky-top"     > -->
          <?= print_menu_xml  ($data, "ru", $site_id_page, $site_url_site, $site_url_rw ) ; ?> 
         </ul>
      </nav>

      <div class="container-fluid" >
        <div class="row">
          <div class="col-md-8">
            <?php if ( isset ( $message ) ): ?>
              <?= "<div class='$message[0]'> $message[1] </div>" ?>
            <?php else: ?> 
                </br> </br> 
                <h1><?= $site_page_act->titre ; ?> </h1>
                <?= str_replace ("<!--url-->", $sitecnf->url, $site_page_act->contenu  ); ?>
                </br> </br> 
            <?php endif ?>
          </div>
          <div class="col-md-4 text-center">
              <?= str_replace ("<!--url-->", $sitecnf->url, $sitecnf->right ) ; ?>
          </div>
        </div> 
    </div>
<?php require ("includes/bs3_footer.php"); ?>