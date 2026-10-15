
<link href="inc/liquidslider/css/liquid-slider.css" rel="stylesheet" />
<link href="inc/liquidslider/css/animate.css" rel="stylesheet" />
<script src="inc/liquidslider/js/jquery.liquid-slider.min.js"></script>
<script src="inc/liquidslider/js/jquery.touchSwipe.min.js"></script>
<script type="text/javascript">
$(function(){
  $('#lstext-1').liquidSlider({
    autoSlide: true,
    pauseOnHover: true,
  });
});
</script>
<style>
.ls-main, #lstext-1-wrapper {
  max-width: 900px !important;
  margin: auto;
}
#lstext-1 .ls-item {
  width: 100%; 
  background: transparent !important;
}
.liquid-slider {
  background: #F2F2F2 !important;
  color: #000000 !important;
}
.ls-nav { background: #F2F2F2 !important; }
ul#lstext-1-nav-ul li a {
  font-weight: bold;
  background: #F2F2F2 !important;
  color: #000000 !important;
}
ul#lstext-1-nav-ul li a:hover, ul#lstext-1-nav-ul li.current a {
  background: #F2F2F2 !important;
  color: #000000 !important;
}
#lstext-1 .ls-select-box select { width: 100%; font-size: 18px; font-weight: bold; }
</style>
<div class="ls-main">
  <div class="liquid-slider" id="lstext-1">
    <div class="ls-item">
      <h2 class="title">Text 1</h2>
      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas metus nulla, commodo a sodales sed, dignissim pretium nunc. Nam et lacus neque. Sed volutpat ante id mauris laoreet vestibulum. Nam blandit felis non neque cursus aliquet. Morbi vel enim dignissim massa dignissim commodo vitae quis tellus. Nunc non mollis nulla. Sed consectetur elit id mi consectetur bibendum. Ut enim massa, sodales tempor convallis et, iaculis ac massa. Etiam suscipit nisl eget lorem pellentesque quis iaculis mi mattis. Aliquam sit amet purus lectus. Maecenas tempor ornare sollicitudin.</p>
    </div>
    <div class="ls-item">
      <h2 class="title">Text 2</h2>
      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas metus nulla, commodo a sodales sed, dignissim pretium nunc. Nam et lacus neque. Sed volutpat ante id mauris laoreet vestibulum. Nam blandit felis non neque cursus aliquet. Morbi vel enim dignissim massa dignissim commodo vitae quis tellus. Nunc non mollis nulla. Sed consectetur elit id mi consectetur bibendum. Ut enim massa, sodales tempor convallis et, iaculis ac massa. Etiam suscipit nisl eget lorem pellentesque quis iaculis mi mattis. Aliquam sit amet purus lectus. Maecenas tempor ornare sollicitudin.</p>
    </div>
  </div>
</div>