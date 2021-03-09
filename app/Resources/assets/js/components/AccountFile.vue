<template>
	<div>
		<div id="account" class="col-sm-12 bg-white pad-15 mrg-t-40">
            <!-- <h2><span class="rounded">4</span> Identifiez-vous</h2> -->

            <div class="row mrg-20-0"  v-if="accountState < 3">
		    	<div class="col-sm-6 text-center">
		    		<button class="btn btn-default btn-rounded mrg-0" v-on:click="clickConnect">
		            	Se Connecter
		            </button>
		    	</div>
		    	<div class="col-sm-6 text-center">
		    		<button class="btn btn-default btn-rounded mrg-0" v-on:click="clickCreateAccount">
		            	Créer un compte
		            </button>
		    	</div>
            </div>
            <div class="row mrg-20-0"  v-if="accountState == 3">
		    	<div class="col-sm-12 text-center">
		    		<button class="btn btn-default btn-rounded mrg-0">
		            	Vous êtes connecté en tant {{ user3dm.firstname }}{{ user3dm.lastname }}
		            </button>
		            <button class="btn btn-default btn-rounded mrg-0" v-on:click="logoutAccount">
		            	Se déconnecter
		            </button>
		    	</div>
            </div>
			<transition name="fade">
		    	<div id="form-login-account" class="col-sm-12"  v-if="accountState == 2">
	    			<h3>Se Connecter :</h3>
	    			<transition name="fade">
	    				<div class="col-sm-12 alert alert-danger" role="alert" v-if="errorLogin">
							{{errorLoginMsg}}
	    				</div>
	    			</transition>
	    			<div class="row">
					    <div class="col-sm-6">
							<input type="text" v-model="loginEmail" @input="interactiveLoginAction" class="form-control" placeholder="Email">
					    </div>
						<div class="col-sm-6">
							<input type="password" v-model="loginPassword" @input="interactiveLoginAction" class="form-control" placeholder="Mot de passe">
					    </div>
					</div>
					<div class="text-right">
						<button class="btn btn-sm mrg-0 btn-default btn-rounded mrg-20-0" v-on:click="loginAccount"> Se Connecter </button> 
					</div>
		    	</div>
	    	</transition>
			<transition name="fade">
		    	<div id="form-create-account" class="col-sm-12" v-if="accountState == 1">
	    			<h3>Créer un compte :</h3>
	    			<transition name="fade">
	    				<div class="col-sm-12 alert alert-danger" role="alert" v-if="errorRegister">
							{{errorRegisterMsg}}
	    				</div>
	    			</transition>
	    			<div class="row">
					    <div class="col-sm-6">
						
							<input type="text" id="create-firstname"  @input="interactiveAction"  v-model="createFirstname" class="form-control" placeholder="Prénom*" >
					    </div>
						<div class="col-sm-6">
							<input type="text" id="create-lastname" @input="interactiveAction" v-model="createLastname" class="form-control" placeholder="Nom*" value="YO"> 
					    </div>
					</div>
					<div class="row">
					    <div class="col-sm-6">
							<input type="text" id="create-email" @input="interactiveAction" v-model="createEmail" class="form-control" placeholder="Email*">
					    </div>
						<div class="col-sm-6">
							<input type="text" id="create-email-confirmation" @input="interactiveAction" v-model="createEmailConfirmation" class="form-control" placeholder="Confirmation email*">
					    </div>
					</div>
					<div class="row">
					    <div class="col-sm-6">
							<input type="password" id="create-password" @input="interactiveAction" v-model="createPassword" class="form-control" placeholder="Mot de passe*">
					    </div>
						<div class="col-sm-6">
							<input type="password" id="create-password-confirmation" @input="interactiveAction" v-model="createPasswordConfirmation" class="form-control" placeholder="Confirmation mot de passe*">
					    </div>
					</div>
					<div class="row">
					    <div class="col-sm-6">
							<input type="text" id="create-company" @input="interactiveAction" v-model="createCompany" class="form-control" placeholder="Société">
					    </div>
						<div class="col-sm-6">
							<input type="text" id="create-phone" @input="interactiveAction" v-model="createPhone" class="form-control" placeholder="Téléphone*">
					    </div>
					</div>
					<div class="row">
					    <div class="col-sm-6">
							<input type="text" id="create-address1" @input="interactiveAction" v-model="createAddress1" class="form-control" placeholder="Adresse*">
					    </div>
						<div class="col-sm-6">
							<input type="text" id="create-address2" @input="interactiveAction" v-model="createAddress2" class="form-control" placeholder="Complément d'adresse">
					    </div>
					</div>
					<div class="row">
					    <div class="col-sm-6">
							<input type="text" id="create-zipcode" @input="interactiveAction" v-model="createZipcode" class="form-control" placeholder="Code postal*">
					    </div>
						<div class="col-sm-6">
							<input type="text" id="create-city" @input="interactiveAction" v-model="createCity" class="form-control" placeholder="Ville*">
					    </div>
					</div>
					<div class="row">
					    <div class="col-sm-6">
					    	<select @input="interactiveAction"  v-model="createCountry" id="createCountry">
					    		<option value="FR" selected="selected">France</option>
					    		<option value="BE">Belgique</option>
					    	</select>
							<!-- <input type="text" v-model="createCountry" class="form-control" placeholder="Pays"> -->
					    </div>
					    <div class="col-sm-6">
							<label>
					            <input type="checkbox" id="newsletter" name="newsletter" value="true" v-model="createNewsletter">
					            <span><!-- fake checkbox --></span>
					            <span class="wrapped-label">Inscription à la newsletter</span>
					        </label>
						</div>
					</div>
					<div class="row">
						
					</div>
					<div class="text-right">
						<button class="btn btn-sm mrg-0 btn-default btn-rounded" v-on:click="createAccount"> Valider </button>
					</div>
		    	</div>
	    	</transition>

    	</div>

    </div>
</template>


<script>
	
	import Vue from 'vue'
	import { mapGetters } from 'vuex'
	import store from '../stores/PrintStore'
	export default {
		name: "accountFile",
		store: store,
		props: [
			'apiUserCreate',
			'apiUserLogin',
			'apiUserLogout',
			'apiUserConnected',
			'type',
		],
		data: function(){
			return {
				accountState: 0,
				firstInteractiveAction: true,
				gtagGroupEvent: '',
				createLastname: null,
				createFirstname: null,
				createEmail: null,
				createEmailConfirmation: null,
				createPassword: null,
				createPasswordConfirmation: null,
				createNewsletter: false,
				createAddress1: null,
				createAddress2: null,
				createCompany: null,
				createCity: null,
				createZipcode: null,
				createCountry: 'FR',
				createPhone: null,
				loginEmail : null,
				loginPassword : null,
				errorLogin: false,
				errorRegister: false,
				errorLoginMsg:false,
				errorRegisterMsg: false,
			}
		},
		mounted (){
			this.userConnected()

			if (this.type == 'print') {this.gtagGroupEvent = 'impression_form'}
			if (this.type == 'design') {this.gtagGroupEvent = 'project_form'}
			if (this.type == 'model') {this.gtagGroupEvent = 'model_form'}
		},
		beforeMounted(){

		},
		computed: {
			...mapGetters([
				'print3dFiles',
				'makersList',
				'user3dm',
			]),
		},
		methods: {
			
			clickConnect : function(){
				// Google Tag Manager : push event account creation view
				//******************************************** */
				gtag_report_event(this.user3dm,this.gtagGroupEvent,'login.view')
				//******************************************** */
				this.accountState=2
				this.firstInteractiveAction=true
			},


			clickCreateAccount : function(){
				// Google Tag Manager : push event account creation view
				//******************************************** */
				gtag_report_event(this.user3dm,this.gtagGroupEvent,this.gtagGroupEvent+'.account_creation.view')
				//******************************************** */
				this.accountState=1
				this.firstInteractiveAction=true
			},
			interactiveAction: function(){

				if (this.firstInteractiveAction == true ) {
					// Google Tag Manager : push event account creation started
					//******************************************** */
					gtag_report_event(this.user3dm,this.gtagGroupEvent,this.gtagGroupEvent+'.account_creation.started')
					//******************************************** */
				}
				this.firstInteractiveAction=false
			},
			interactiveLoginAction: function(){

				if (this.firstInteractiveAction == true ) {
					// Google Tag Manager : push event account creation started
					//******************************************** */
					gtag_report_event(this.user3dm,this.gtagGroupEvent,'login.started')
					//******************************************** */
				}
				this.firstInteractiveAction=false
			},

			createAccount : function(){

				let validate = true
				this.errorRegister = false

				// Google Tag Manager : push event account creation attempt
				//******************************************** */
				gtag_report_event(this.user3dm,this.gtagGroupEvent,this.gtagGroupEvent+'.account_creation.attempt')
				//******************************************** */

			    if(!this.createFirstname){
			    	$("#create-firstname").addClass("required-field")
			    	validate = false
			    } else {
			    	$("#create-firstname").removeClass("required-field")
			    }

			    if(!this.createLastname){
			    	$("#create-lastname").addClass("required-field")
			    	validate = false
			    } else {
			    	$("#create-lastname").removeClass("required-field")
			    }

			    if(!this.createEmail){
			    	$("#create-email").addClass("required-field")
			    	validate = false
			    } else {
			    	$("#create-email").removeClass("required-field")
			    	if(!this.createEmailConfirmation || (this.createEmailConfirmation.toUpperCase() != this.createEmail.toUpperCase())){

			    		$("#create-email-confirmation").addClass("required-field")
			    		this.errorRegister = true
			    		this.errorRegisterMsg = 'Confirmation email incorrect'
			    		validate = false

			    	} else {

			    		$("#create-email-confirmation").removeClass("required-field")

			    	}
			    }

			    if(!this.createPassword){
			    	$("#create-password").addClass("required-field")
			    	validate = false
			    } else {

			    	$("#create-password").removeClass("required-field")

			    	if(!this.createPasswordConfirmation || (this.createPasswordConfirmation != this.createPassword)){

			    		$("#create-password-confirmation").addClass("required-field")
			    		this.errorRegister = true
			    		this.errorRegisterMsg = 'Confirmation mot de passe incorrect'
			    		validate = false

			    	} else {

			    		$("#create-password-confirmation").removeClass("required-field")

			    	}
			    }

			    if(!this.createPhone){
			    	$("#create-phone").addClass("required-field")
			    	validate = false
			    } else {
			    	$("#create-phone").removeClass("required-field")
			    }

			    if(!this.createAddress1){
			    	$("#create-address1").addClass("required-field")
			    	validate = false
			    } else {
			    	$("#create-address1").removeClass("required-field")
			    }

			    if(!this.createZipcode){
			    	$("#create-zipcode").addClass("required-field")
			    	validate = false
			    } else {
			    	$("#create-zipcode").removeClass("required-field")
			    }

			    if(!this.createCity){
			    	$("#create-city").addClass("required-field")
			    	validate = false
			    } else {
			    	$("#create-city").removeClass("required-field")
			    }

			    if(!validate){
			    	return
			    }

				let data = {
					'firstname' : this.createFirstname  ,
					'lastname' : this.createLastname ,
					'email' : this.createEmail ,
					'password' : this.createPassword ,
					'newsletter' : this.createNewsletter ,
					'company' : this.createCompany,
					'phone' : this.createPhone,
					'address1' : this.createAddress1,
					'address2' : this.createAddress2,
					'zipcode' : this.createZipcode,
					'city' : this.createCity,
					'country' : this.createCountry,
					'maker': ''
				}
				this.$http.post(this.apiUserCreate, data ).then((response) => 
				{
					console.log('Account 1 : API USer Create => success')

					var data = response.body
					store.commit('UPDATE_USER_3DM', data)
					
					this.accountState = 3
					this.errorRegister = false
					
					// Google Tag Manager : push event account creation success
					//******************************************** */
					gtag_report_event(this.user3dm,this.gtagGroupEvent,this.gtagGroupEvent+'.account_creation.success')
					//******************************************** */

					if(this.type == 'print'){
						store.commit('CHANGE_STEP',5)
	  					setTimeout(function() {$('html,body').animate({scrollTop: $('#address').offset().top},'slow');}, 200);
					}

					// Facebook Pixel
                    fbq('track', 'Subscribe', {currency: "EUR", value: 50.00});

				}, (response) => {
					console.log('API User Create => error')
					this.errorRegister = true
					this.errorRegisterMsg = 'Erreur lors de la création de votre compte'
				})
			},
			loginAccount : function(){
					
				// Google Tag Manager : push event account creation success
				//******************************************** */
				gtag_report_event(this.user3dm,this.gtagGroupEvent,'login.attempt')
				//******************************************** */

				let data = {					
				    "security": {
				        "credentials": {
				            "login": this.loginEmail ,
				            "password": this.loginPassword
				        }
				    }
				}
				this.$http.post(this.apiUserLogin, data ).then((response) => 
				{
					console.log('AccountVue 2: API USer Login => success')
					var data = response.body.data
					store.commit('UPDATE_USER_3DM', data)
					this.accountState = 3;
					this.errorLogin = false;

					if(this.type == 'print'){
						store.commit('CHANGE_STEP',5)
	  					setTimeout(function() {$('html,body').animate({scrollTop: $('#address').offset().top},'slow');}, 500);
					}
				// Google Tag Manager : push event account creation success
				//******************************************** */
				gtag_report_event(this.user3dm,this.gtagGroupEvent,'login.success')
				//******************************************** */

				}, (response) => {
					console.log('API User Login => error')
					this.errorLogin = true
					this.errorLoginMsg = 'Identifiants invalides ou compte inconnu'
				})
			},
			logoutAccount : function(){
				
				this.$http.post(this.apiUserLogout ).then((response) => 
				{
					console.log('Accounte Vue 3 : API USer Logout => success')
					//var data = response.body.data
					//store.commit('UPDATE_USER_3DM', data)
					this.accountState = 0;
					this.errorLogin = false;

					if(this.type == 'print'){
						store.commit('CHANGE_STEP',4);
					}

					let data = {}
					store.commit('UPDATE_USER_3DM', data)
	  				//setTimeout(function() {$('html,body').animate({scrollTop: $('#address').offset().top},'slow');}, 500);

				}, (response) => {
					console.log('API User Logout => error')
					this.errorLogin = true
					this.errorLoginMsg = 'Problème de deconnexion'
				})
			},
			userConnected : function(){
				this.$http.post(this.apiUserConnected ).then((response) => 
				{
				
					var data = response.body.data
					console.log('AcountVue 4 : API USer Connected => success')
					store.commit('UPDATE_USER_3DM', data)
					this.accountState = 3
					this.errorLogin = false
	  				//setTimeout(function() {$('html,body').animate({scrollTop: $('#address').offset().top},'slow');}, 500);
				}, (response) => {
					console.log('API User Login => error')
					store.commit('UPDATE_USER_3DM', {})
					this.accountState = 0
				})
			}
			
		},
	}
</script>
<style>

</style>