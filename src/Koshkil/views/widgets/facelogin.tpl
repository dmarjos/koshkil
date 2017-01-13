{if Application::get("FACEBOOK_APP_ID")}
<div id="fb-root"></div>
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '{Application::get("FACEBOOK_APP_ID")}',
      xfbml      : true,
      version    : 'v2.5'
    });
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {
     	return;
     }
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/es_AR/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
</script>
{/if}