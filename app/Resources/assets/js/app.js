import Vue from 'vue'
import Vuex from 'vuex'
import VueResource from 'vue-resource'
import AppPrint from './components/App.vue'
import store from './stores/PrintStore.js'
import VeeValidate from 'vee-validate'
import * as VueGoogleMaps from "vue2-google-maps";


Vue.use(Vuex)
Vue.use(VueResource)
Vue.use(VeeValidate)
Vue.use(VueGoogleMaps, {
  load: {
    key: googleApiKey,
    libraries: "places, distancematrix"
  }
});

var vm = new Vue({
	el: '#app-init',
	store,
	...AppPrint
});

$(document).ready(function () {

	var isActive= false;
    $("#how-it-work").click(function(e){
    	
    	if(!isActive){
    		isActive= true;
    		var switcher= $("#how-it-work > :last-child");
	    	if(this.className.indexOf(' inactive') > -1){
			    this.className = this.className.replace(" inactive", "");
	    		$("#how-it-work > :last-child").toggle(1000);
	    	} else {
	    		if(e.target.tagName == 'H2'){
			    	this.className += ' inactive'
	    			$("#how-it-work > :last-child").toggle(1000);
	    		}
	    	}
    	}
	    setTimeout(function() {isActive= false}, 1050);	

	});
	
    if(document.getElementById('cart-sumup')) {

    	var ref= document.getElementById('js-scrollref');
		var startValue = ref.offsetTop;


	    window.addEventListener('scroll', function(){

	    	var endValue= startValue + ref.offsetHeight;
	  		var card= document.getElementById('cart-sumup');
			var height = card.offsetHeight;

	    	if(window.pageYOffset < startValue || window.pageYOffset >= endValue-height-10){
	    		card.style.position= "relative";
	    		if(window.pageYOffset >= endValue-height-10){
	    			card.style.top= ref.offsetHeight-height + "px";
	    		} else {
	    			card.style.top= "0px";
	    		}
	    	} else {
	    		card.style.position= "fixed";
	    		card.style.top= "0px";
	    	}

	    });
    }
    
});