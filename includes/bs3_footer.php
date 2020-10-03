<!-- Footer -->
 <footer >
 <div class="row w3-center  <?=$sitecnf->theme;?>  darken-4">
    <?=  $sitecnf->right ;  ?>
   <div class="col s12 black white-text w3-padding w3-center"> <?=  $sitecnf->footer ;  ?> </div>
  </div>
</footer>
  
<!-- End page content -->
</div>

<!--JavaScript at end of body for optimized loading-->
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/materialize.min.js"></script>


<script>
//M.AutoInit();

$(document).ready(function(){
  $('.scrollspy').scrollSpy();
  $('.slider').slider();
  $('.tooltipped').tooltip();
  $('.sidenav').sidenav();
});

// Script to open and close sidebar
function w3_open() {
    document.getElementById("mySidebar").style.display = "block";
    document.getElementById("myOverlay").style.display = "block";
}
 
function w3_close() {
    document.getElementById("mySidebar").style.display = "none";
    document.getElementById("myOverlay").style.display = "none";
}

</script>
</body>
</html>