 <!-- Footer -->
 <footer class="w3-container w3-padding-32 w3-theme-d5">
<table  style="width:100%;" >
<tr>
<td  class="w3-border-right"  style="width:33%;text-align:center;" >
<div> <a href="https://www.facebook.com/domashniypersonal/" target="_blank"><IMG src="images/facebook.gif" alt="Агентство Домашний персонал" ></a>  </div>
<div> <a href="https://www.facebook.com/domashniypersonal/" target="_blank"><IMG src="images/fb.png" alt="Агентство Домашний персонал" > 
<br> Агентство Домашний персонал</a> </div>
</td> <td class="w3-border-right"  style="width:33%;text-align:center;">  
<div> <br> <a href="http://www.repetitor-kiev.com" > <img src="images/banstaffkiev.png"/>  <br> www.Repetitor-kiev.com </a> </div>
</td> <td style="text-align:center;"> 
<div> <a href="http://klumba.ua/" target="_blank"><IMG src="http://klumba.ua/static/gfx/klumba.png" alt="Каталог детских товаров Клумба" width="61" height="62" ></a> </div>
<div> <strong>реклама</strong> </div>

</td> 
</tr>
</table>
</footer>
  
  <div class="w3-black w3-center w3-padding-24"> <?=  $sitecnf->footer ;  ?> </div>

<!-- End page content -->
</div>

<script>
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

<!--JavaScript at end of body for optimized loading-->
<script type="text/javascript" src="js/materialize.min.js"></script>


<script>
    window.onscroll = function() {myFunction()};

    var navbar = document.getElementById("navbar");
    var sticky = navbar.offsetTop;

    function myFunction() {
      if (window.pageYOffset >= sticky) {
        navbar.classList.add("sticky") ;
        document.getElementById("navbar_logo").innerHTML =  '<span class="w3-large">Staff-kiev.com </span>' ;
        document.getElementById("navbar_logo2").innerHTML = '<span class="fa fa-phone w3-large"> 0932901228 - 0673355979</span>' ;

      } else {
        navbar.classList.remove("sticky");
        document.getElementById("navbar_logo").innerHTML = "" ;
        document.getElementById("navbar_logo2").innerHTML = "" ;
 
       }
    }


    $(document).ready(function(){
  // Add smooth scrolling to all links in navbar + footer link
  $(".navbar a, footer a[href='#myPage']").on('click', function(event) {

    // Prevent default anchor click behavior
    event.preventDefault();

    // Store hash
    var hash = this.hash;

    // Using jQuery's animate() method to add smooth page scroll
    // The optional number (900) specifies the number of milliseconds it takes to scroll to the specified area
    $('html, body').animate({
      scrollTop: $(hash).offset().top
    }, 900, function(){
   
      // Add hash (#) to URL when done scrolling (default click behavior)
      window.location.hash = hash;
    });
  });
  
  // Slide in elements on scroll
  $(window).scroll(function() {
    $(".slideanim").each(function(){
      var pos = $(this).offset().top;

      var winTop = $(window).scrollTop();
        if (pos < winTop + 600) {
          $(this).addClass("slide");
        }
    });
  });
})
</script>
</body>
</html>