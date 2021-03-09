<template>
	<div>
		<div id="payment" class="col-sm-12 bg-white pad-15 mrg-t-40">
            <h2>
            	<span class="rounded" v-if="type == 'design' && shippingRequired != true"> 
            		4
            	</span>
				<span class="rounded" v-else-if="type == 'basket'"> 
            		2
            	</span> 
            	<span class="rounded" v-else> 
            		6
            	</span> 
            	Payez votre commande
            </h2>
    	
	    	<div class="checkbox checkbox-inline pad-0-15" v-show="!statePayment">
		        <label>
		            <input type="checkbox"  id="cgv" @change="interactiveAction"   name="cgv" value="true" v-model="checkboxCgv">
		            <span><!-- fake checkbox --></span>
		            <span class="wrapped-label">J'accepte les <a target="_blank" href="https://www.united-3dmakers.com/conditions-generales-utilisation-vente/">conditions générales d'utilisation et de vente</a></span>
		        </label>
		    </div>
		    <br>
		    <br>
		    <div id="module-stripe" v-show="!statePayment" v-bind:class="{ 'allow-payment': checkboxCgv , 'disabledbutton' : !checkboxCgv }" >

		    	<!-- PAYMENT MODE BLOCK -->
		    	<div v-if="type != 'basket'" id="payment-mode" class="pad-0-15">
		    		<label for="payment-mode-card">
	            		<div class="radio mrg-0">
	            			<label>
		            			<input type="radio" name="shippingMode" id="payment-mode-card" value="card" v-model="paymentMode"/>
		            			<span></span>
		            			<span>Paiement par carte bancaire</span>
	            			</label>
	            		</div>
	            	</label>
	            	<label for="payment-mode-virement" class="mrg-l-20">
	            		<div class="radio mrg-0">
	            			<label>
		            			<input type="radio" name="shippingMode" id="payment-mode-virement" value="virement"  v-model="paymentMode"/>
		            			<span></span>
		            			<span>Paiement par virement</span>
	            			</label>
	            		</div>
	            	</label>
					<label for="payment-mode-sepa" v-show="'A'=='B'" class="mrg-l-20">
	            		<div class="radio mrg-0">
	            			<label>
		            			<input type="radio" name="shippingMode" id="payment-mode-sepa" value="sepa"  v-model="paymentMode"/>
		            			<span></span>
		            			<span>Paiement par prélèvement SEPA</span>
	            			</label>
	            		</div>
	            	</label>
		    	</div>
				<!-- END PAYMENT MODE BLOCK -->

				<transition name="fade">
	    			<div class="col-sm-12 alert alert-danger" role="alert" v-if="errorPayment || stripeError!= ''">
						<!-- {{errorPayment}} -->
						{{stripeError}}
	    			</div>
	    		</transition>
		    	<form>
					<div class="text-left">
					   <!-- <p class="stripeError" v-if="stripeError">
					      
					   </p> -->
					</div>

					<!-- PAYMENT CARD -->
					<div class="col-sm-12" v-show="paymentMode == 'card'">
						<div class="col-sm-3 float-left">
							<label class="form-label" for="Card Number">
						    	Numéro de carte bancaire
							</label>
							<div class="form-controls">
						      <div id="card-number" class="input" :class="{ 'form-danger': cardNumberError }"></div>
							</div>
							<span class="help-block" v-if="cardNumberError">
						         {{cardNumberError}}
						    </span>
						</div>
						<div class="col-sm-2 float-left">
						    <label class="form-label" for="Card CVC">
						       Numéro CVC
						    </label>
						    <div class="form-controls">
						        <div id="card-cvc" class="input" :class="{ 'form-danger': cardCvcError }"></div>
						    </div>
						    <span class="help-block" v-if="cardCvcError">
						         {{cardCvcError}}
						    </span>
						</div>
						<div class="col-sm-2 float-left">
						    <label class="form-label" for="Expiry Month">
						         Date d'expiration
						    </label>
						   	<div class="form-controls">
						        <div id="card-expiry" class="input" :class="{ 'form-danger': cardExpiryError }"></div>
						    </div>
						    <span class="help-block" v-if="cardExpiryError">
						        {{cardExpiryError}}
						    </span>
						</div>
					</div>
					<!-- END PAYMENT CARD -->

					<!-- PAYMENT SEPA -->
					<div class="col-sm-12" v-show="paymentMode == 'sepa'">
						<div class="col-sm-12 float-left" v-show="!paymentModeAuthorize">
						   Pour effectuer vos prochains paiements par prélèvement SEPA, <br>
						   merci de nous adresser votre demande par email à <u><a :href="'mailto:'+emailContact+'?subject=Demande de Paiement par Prélèvement SEPA'">{{ emailContact }}</a></u>
						   <br><br>
						</div>
						<div v-show="paymentModeAuthorize">
							<div class="form-row">
								<div class="col-sm-13 float-left">
									<label class="form-label" for="Card Number">
								    	IBAN
									</label>
									<div class="form-controls">
								      <div id="iban-element" class="input" :class="{ 'form-danger': ibanNumberError }"></div>
									</div>
									<span class="help-block" v-if="ibanNumberError">
								         {{ibanNumberError}}
								    </span>
								</div>
							</div>
							<div id="bank-name"></div>
							<br>
							<p class="txt-legal-sepa">
							En fournissant votre IBAN et en confirmant ce paiement, vous autorisez The Gator Projects (éditeur du site wwww.united-3dmakers.com) et Stripe, notre prestataire de services de paiement, à envoyer des instructions à votre banque pour qu'elle débite votre compte, et à votre banque pour débiter votre compte conformément à ces instructions. Vous avez droit à un remboursement de votre banque selon les termes et conditions de votre contrat avec votre banque. Un remboursement doit être demandé dans les 8 semaines à compter de la date à laquelle votre compte a été débité.
							</p>
						</div>
					</div>
					<!-- END PAYMENT SEPA -->
					<!-- PAYMENT VIREMENT -->
					<div class="col-sm-12" v-show="paymentMode == 'virement'">
						<div class="col-sm-12 float-left" >
						   Virement à effectuer vers le compte : 
						   <b>BNP PARIBAS - The Gator Projects <br> IBAN: FR76 3000 4008 3200 0103 4284 218 <br>
						   	RIB:  30004 00832 0010342842 18 </b><br>
							<br>Préciser la référence de la commande dans le libellé du virement.<br>
							A la reception du virement, une notification vous sera envoyée.
						   <br><br>
						</div>
					</div>
					<!-- END PAYMENT VIREMENT -->
					<div class="col-sm-12 text-right">
					   <button class="btn btn-default btn-rounded" @click.prevent="submitFormToCreateToken()" v-show="!loading">
					     <span>Payer ma commande</span>
					   </button>
					   <button class="btn btn-default btn-rounded" v-show="loading" disabled>
					     <span>Traitement en cours ...</span>
					   </button>
					</div>
				</form>

		    </div>
		    <div  v-show="statePayment">
		    	<transition name="fade">
					<div v-if="type != 'basket'" class="col-sm-12 text-center" >
						
						<p>Nous vous remercions.
						Un mail récapitulatif de la commande vous a été envoyé.</p>

						<p>Vous pouvez également suivre l'avancement du traitement de votre commande dans votre espace personnel Mon Compte / Mes commandes.</p>

						<p>L'équipe United 3D Makers.</p>

						<button v-on:click="refreshPage()" class="btn btn-default btn-rounded">{{label1_1}}</button>
					
					</div>
					<div v-else class="col-sm-12 text-center" >
						<p>United 3d Makers vous remercie de votre commande sur notre plateforme.
						Un mail récapitulatif vous a été envoyé.</p>

						<p>Vous pouvez télécharger le(s) modèle(s) que vous avez acheté(s)
							<a :href="commandUrl">
							 	<u> dans vos commandes United 3D Makers.</u>
							</a>
						</p>

						<p>L'équipe United 3D Makers.</p>
						<a :href="modelUrl" class="btn btn-default btn-rounded">Chercher d'autres modèles</a>
					</div>
		    	</transition>
		    </div>
	    </div>	
	</div>
</template>


<script>

	/* INSPIRED by :
		https://medium.com/@damijolayemi/how-to-use-vuejs-with-stripe-elements-and-uikit-192a7be4f57
	*/
	
	import Vue from 'vue'
	import { mapGetters } from 'vuex'
	import store from '../stores/PrintStore'


	export default {
		name: "paymentFile",
		store: store,
		props: [
			'stripeApiKey',
			'apiPayment',
			'apiThirdDSecure',
			'apiOrder',
			'apiBasketorder',
			'apiPaymentcancel',
			'emailContact',
			'type',
			'urlNewProject',
			'shippingRequired',
			'modelUrl',
			'commandUrl',
			'tagSpecial'
		],
		data: function(){
			return {
				stripeInit: false,
				gtagGroupEvent: '',
				firstInteractiveAction: true,
				card: {
				    cvc: '',
				    number: '',
				    expiry: ''
			    },

			    //elements
			    cardNumber: '',
			    cardExpiry: '',
			    cardCvc: '',
			    stripe: null,
			    iban:'',

			    // errors
			    stripeError: '',
			    cardCvcError: '',
			    cardExpiryError: '',
			    cardNumberError: '',

			    ibanNumberError:'',

			    loading: false,
			    checkboxCgv: false,
			    errorPayment : false,
			    statePayment : false,

			    paymentMode : 'card',  // card or sepa
				token: null,
				label1_1 : '',
			    //paymentIntent : null,

			}
		},
		mounted (){

			this.label1_1 = 'Créer un nouveau projet';
			switch (tagSpecial) {
				case 'COVID' :
					this.label1_1 = "Faire une nouvelle demande";
					break;
				case 'PRINT' :
					this.label1_1 = "Commander une nouvelle impression";
					break;					
			}
			if (this.type == 'print') {this.gtagGroupEvent = 'impression_form'}
			if (this.type == 'design') {this.gtagGroupEvent = 'project_form'}
			if (this.type == 'model') {this.gtagGroupEvent = 'model_form'}
			
			//this.setUpStripe()

		},
		computed: {
			...mapGetters([
				'print3dFiles',
				'makersList',
				'stepFormProcess',
				'stepFormProject',
				'makerSelected',
				'shippingSelected',
				'uploadProcess',
				'orderId',
				'addressBilling',
				'addressShipping',
				'user3dm',
				'fees',
				'instruction',
				'discount_excl',
				'discount_incl',
				'coupon',
				'projectOrigin',
				'currentBasket',
				'stripeIntentSecret',
				'totalTtc'
			]),


			paymentModeAuthorize: function(){

				if(this.user3dm.sepa === true){

					//this.paymentMode = 'sepa'
					return true

				} else {

					//this.paymentMode = 'card'
					return false

				}

			},
		},
		methods: {
			interactiveAction: function(){
				if (this.firstInteractiveAction == true ) {
					// Google Tag Manager : push event payment started
					//******************************************** */
					gtag_report_event(this.user3dm,this.gtagGroupEvent,this.gtagGroupEvent +'.payment.started')
					//******************************************** */
				}
				this.firstInteractiveAction=false
			},
			setUpStripe() {

		        if (window.Stripe === undefined) {
		          //console.log('Stripe V3 library not loaded!')
		        } else {

		          this.stripeInit = true
		          const stripe = window.Stripe(this.stripeApiKey)
		          this.stripe = stripe

		          const elements = stripe.elements()

		          this.cardCvc = elements.create('cardCvc')
		          this.cardExpiry = elements.create('cardExpiry')
		          this.cardNumber = elements.create('cardNumber')

		          this.cardCvc.mount('#card-cvc')
		          this.cardExpiry.mount('#card-expiry')
		          this.cardNumber.mount('#card-number')

				  this.ibanElement = elements.create('iban', {
					supportedCountries: ['SEPA'],
				  })

				  this.ibanElement.mount('#iban-element')

		          this.listenForErrors()
		        }
      		},

			listenForErrors() {

	        	const vm = this;

	        	this.cardNumber.addEventListener('change', (event) => {
	        		vm.toggleError(event)
	        		vm.cardNumberError = ''
	        		vm.card.number = event.complete ? true : false
	        	});
					
	        	this.cardExpiry.addEventListener('change', (event) => {
	        		vm.toggleError(event)
	        		vm.cardExpiryError = ''
	        		vm.card.expiry = event.complete ? true : false
	        	});
	        
				this.cardCvc.addEventListener('change', (event) => {
	          		vm.toggleError(event)
	          		vm.cardCvcError = ''
	          		vm.card.cvc = event.complete ? true : false
	        	});

	        	this.ibanElement.addEventListener('change', (event) => {
	          		vm.toggleError(event)
	          		vm.ibanNumberError = ''
	          		vm.iban = event.complete ? true : false
	        	});

      		},
			toggleError (event) {
		        if (event.error) {
		          this.stripeError = event.error.message;
		        } else {
		          this.stripeError = '';
		        }
      		},

		    submitFormToCreateToken() {
				// Google Tag Manager : push event account creation started
				//******************************************** */
				gtag_report_event(this.user3dm,this.gtagGroupEvent,this.gtagGroupEvent +'.payment.attempt')
				//******************************************** */
		    	this.loading = true
		    	this.errorPayment = false
				this.stripeError = '';

		        this.clearCardErrors()
		        this.clearIbanErrors()
		        let valid = true;

		        if(this.paymentMode == 'card'){

			        if (!this.card.number) {
			          valid = false
			          this.cardNumberError = "Numéro incorrect";
			        }
			        if (!this.card.cvc) {
			          valid = false
			          this.cardCvcError = "CVC incorrect";
			        }
			        if (!this.card.expiry) {
			          valid = false
			          this.cardExpiryError = "Date incorrecte";
			        }

		    	} else if(this.paymentMode == 'sepa'){

			        if (!this.iban) {
			          valid = false
			          this.ibanNumberError = "IBAN incorrect";
			        }

		    	}

		        if (this.stripeError) {
		          valid = false
		        }

		        if (valid) {

		          	this.createToken()
					  
					if (!this.errorPayment  && this.loading  && this.stripeError == '') {
						// payment success
						// Google Tag Manager : push event account creation started
						//******************************************** */
						gtag_report_event(this.user3dm,this.gtagGroupEvent,this.gtagGroupEvent +'.payment.success')
						//******************************************** */
					}
		        } else {
		        	this.loading = false
		        }
				
		    },

		    createToken() {
				console.log('API Payment Create => createtoken')
		    	// For credit Card
		    	if(this.paymentMode == 'card'){

		    		//this.stripe.createToken(this.cardNumber).then((result) => {
					this.stripe.createPaymentMethod('card', this.cardNumber, {
    					billing_details: {name: this.user3dm.firstname + ' '+ this.user3dm.lastname }
    				}
					).then((result) => {
			            if (result.error) {

							this.stripeError = result.error.message;
							this.loading = false

			            } else {

			            	//console.log('YOUPI => ', result);
			            	this.token = result.paymentMethod.id;
			            	this.thirdDSecure(this.token);

			            }
		        	});

				// For IBAN
		    	} else if(this.paymentMode == 'sepa'){

		    		let sourceData = {
						type: 'sepa_debit',
						currency: 'eur',
						owner: {
					    	name: this.user3dm.firstname + ' ' + this.user3dm.lastname,
					    	email: this.user3dm.email,
					    }
					}

		    		this.stripe.createSource(this.ibanElement, sourceData).then((result) => {
						if (result.error) {
							
							this.stripeError = result.error.message;
							this.loading = false
							
						} else {

							//console.log('Token SEPA',result.source.id);
							this.token = result.source.id
							this.makerOrder(this.token,null)

					    }
				  	});
		    	} else if(this.paymentMode == 'virement'){
					this.makerOrder(null,null)
				}
				
		    },
    		clearElementsInputs() {
		        this.cardCvc.clear()
		        this.cardExpiry.clear()
		        this.cardNumber.clear()
		        this.iban.clear()
    		},
		    clearCardErrors() {
		        this.stripeError = ''
		        this.cardCvcError = ''
		        this.cardExpiryError = ''
		        this.cardNumberError = ''
		    },
		    clearIbanErrors() {
		    	this.stripeError = ''
		        this.ibanNumberError = ''
		    },
		    validateOrder(token,paymentMethodId = null) {

		    	console.log('Function validateOrder');

				//console.log('coucou')
				//console.log(this.orderId)
				
		    	let data = {
		    		'order_id' : this.orderId,
		    		'token' : token,
		    		'type' : this.paymentMode,
					'orderType' : this.type,
		    		'payment_method_id': paymentMethodId
		    	}

		    	this.$http.post(this.apiPayment, data ).then((response) => 
				{
					//console.log('API Payment Create => success',response)
					var data = JSON.parse(response.body)

					//console.log('DATA response order', data)
					this.statePayment = true
					this.loading = false
					store.commit('CHANGE_STEP',9)
					store.commit('CHANGE_STEP_PROJECT',9)
					$("#dynamicBasket searchCategory").hide();

				}, (response) => {
					console.log('API Payment Create => error :',response)
					//console.log('API Payment Create => error',response)
					
					if(this.type == "basket") {
						this.$http.post(this.apiPaymentcancel, data).then((findData) => 
						{
							console.log('API Order Modified => cancel')

						}, (findData) => {
							console.log('API Order Modified => error')
						})
					}
					var data = JSON.parse(response.body)

					//console.log('API Payment Create => error',data.message)
					this.loading = false
					this.errorPayment = true
					this.stripeError = "Un problème est survenu avec votre carte, vous n'avez donc pas été débité";//data.message

				})
				
				//$("#dynamicBasket searchCategory").empty();
				//$("#dynamicBasket searchCategory").append('Mon panier');
				
		    },
			cancelPayment(paymentIntentId) {

		    	console.log('Function Cancel Paymentintent');

		    	let data = {
		    		'cancel_intent' : true,
					'payment_id' : paymentIntentId
		    	}

		    	this.$http.post(this.apiPayment, data ).then((response) => 
				{
					console.log('API Payment cancel => success',response)
					var data = JSON.parse(response.body)

				}, (response) => {
					console.log('API Payment Cancel => error :',response)
					this.loading = false
					this.errorPayment = true
					this.stripeError = "Erreur sur la confirmation de la carte. Vous ne serez pas débité";//data.message

				})
				
		    },
			reset() {
				this.clearElementsInputs()
				this.clearCardErrors()
				this.clearIbanErrors()
			},
			refreshPage(){
				if(this.type != 'design'){

					document.location.reload(true);

				} else {
					
					document.location.href = this.urlNewProject;

				}
				
			},
			thirdDSecure(paymentMethod){
				
				let totalThirdSecure = this.totalTtc;

				let total_amount_tax_incl = 0
				if(this.type === 'basket'){
					//console.log('yhouhou')
					var priceHT = 0;
					var priceTTC = 0;
					for (var item in this.currentBasket) {
						//console.log(this.currentBasket);
						priceHT += this.currentBasket[item].priceTaxExcl;
						priceTTC += this.currentBasket[item].priceTaxIncl;
					}
					total_amount_tax_incl = priceTTC*100;
				} else {
					total_amount_tax_incl = this.totalTtc;
				}
			
				let dataThird = {
						"thirdDSecure": paymentMethod,
						"amount": total_amount_tax_incl,
						"user_id": this.user3dm.id,
						'orderType' : this.type
					};

				var self = this


				this.$http.post(this.apiThirdDSecure, dataThird ).then((response) => 
					{
						//console.log('API 3D Secure => success',response)

							var dataThird = JSON.parse(response.body)
							//console.log('3D Secure :',dataThird.error);

							if (dataThird.error) {
								// Show error from server on payment form
								//console.log('3D Secure En erreur',dataThird);
								self.loading = false
								self.errorPayment = true
								self.stripeError =  "Echec dans l'authentification de votre carte. Le paiement a été annulé (" + dataThird.error + ")" ;


							} else if (dataThird.requires_action) {
								// Use Stripe.js to handle required card action
								//console.log('3D secure required');
							    this.stripe.handleCardAction(dataThird.payment_intent_client_secret).then(function(result) {
									if (result.error) {
										console.log('3D secure error sur le handleCardAction %s',dataThird.payment_intent_id);
										self.loading = false
										self.errorPayment = true
										//self.stripeError = result.error.message;
										self.stripeError = "Echec dans l'authentification de votre carte. Le paiement a été annulé ";

										self.cancelPayment(dataThird.payment_intent_id)

										// Show error in payment form
									} else {
										//console.log('Token after 3D avant le parse', token);
										let token = JSON.parse(response.body);

										//console.log('Token after 3D apres le parse ', token);

										let paymentIntentId = token.payment_intent_id;

										self.makerOrder(paymentIntentId,token.payment_method_id);

									}
								});
							} else {


								self.makerOrder(dataThird.payment_intent_id,dataThird.payment_method_id);

							}


						
					}, (response) => {

						console.log('API 3D Secure => error Et rien ne se passe',response)

					})
				console.log('API Payment Create => thirdEnd')
			},
			makerOrder(token = null, paymentMethodId = null){
				console.log('API Payment Create => makerOrder')
				this.token = token;

				let tagProducts =[];
				let tagPurchase = {
					"ecommerce": {
						"purchase": {
							"actionField": {
								"id": 0,
								"affiliation": null,
								"revenue":0,
								"tax":0,
								"fee":0,
								"shipping":0,
								"coupon": null
							},
						"products":[]
						}
					}
				}

	  			//store.commit('UPDATE_PROCESS_UPLOAD',{'state': false})
	  			let items = []
	  			let total_amount_tax_incl = 0
	  			let total_amount_tax_excl = 0

	  			if(this.type === 'basket'){
					//console.log('yhouhou')
					var priceHT = 0;
					var priceTTC = 0;
					for (var item in this.currentBasket) {
						//console.log(this.currentBasket);
						priceHT += this.currentBasket[item].priceTaxExcl;
						priceTTC += this.currentBasket[item].priceTaxIncl;
					}
					total_amount_tax_incl = priceTTC;
		  			total_amount_tax_excl = priceHT;
				} else if(this.type != 'design'){

	  				for (const a of this.print3dFiles) {

		  				let tabOption = []
		  				let tabOptionExcl = 0
		  				let tabOptionIncl = 0

		  				for(const option of a.finishing){

		  					let optionElement = {
		  						"finishing": option.id,
		  						"amount_tax_incl": option.pricettc,
		  						"amount_tax_excl": option.priceht
		  					}

		  					tabOption.push(optionElement)
		  					tabOptionIncl += option.pricettc
		  					tabOptionExcl += option.priceht

		  				}

		  				let product = { 
		  					"file_id": a.fileDb, // id entity PrintFile
		  					"file_name": a.filename,
					        "amount_tax_incl": a.priceTaxInc, // montant total pour cet objet (= unitaire * quantité + setup), sans les finitions
					        "amount_tax_excl": a.priceTaxEcl,
					        "quantity": a.quantity,
					        "dimensions": {
					            "x": a.dimensions.x, // dimension en float (mm)
					            "y": a.dimensions.y,
					            "z": a.dimensions.z
					        },
					        "volume": a.volume, // volume en float (mm3)
					        "technology": a.technology,  // id technology
					        "material": a.material,    // id material
					        "layer": a.layer,       // id layer
					        "color": a.color,       // id color
					        "fillingRate": a.filling, // id filling rate : 0, 1, 2 ou 3
					        "finishings": tabOption
		  				}

		  				total_amount_tax_incl += a.priceTaxInc + tabOptionIncl
		  				total_amount_tax_excl += a.priceTaxEcl + tabOptionExcl
		  				items.push(product)

						// TAG Manager : set purchase product
						let tagProduct = {
							"name": a.technologyLabel,
							"id": a.material,
							"variant": a.materialLabel,
							"price": a.priceTaxEcl + tabOptionExcl,
							"quantity":a.quantity,
							"coupon": null,
							"brand" : this.makerSelected.name
						}
						tagProducts.push(tagProduct)
		  			}

	  			} else {
					// type Design
					
	  				total_amount_tax_incl = this.makerSelected.price_incl;
		  			total_amount_tax_excl = this.makerSelected.price_excl;

					let tagProduct = {
						"name": this.makerSelected.quotation.reference,
						"id": this.makerSelected.quotation.id,
						"price": total_amount_tax_excl,
						"quantity":1,
						"coupon": null,
						"brand" : this.makerSelected.name
					}
					tagProducts.push(tagProduct)

	  			}
	  			
				tagPurchase["ecommerce"]["purchase"]["products"] = tagProducts
				tagPurchase["ecommerce"]["purchase"]["actionField"]["affiliation"] = this.makerSelected.name
				tagPurchase["ecommerce"]["purchase"]["actionField"]["revenue"] = total_amount_tax_excl;
				tagPurchase["ecommerce"]["purchase"]["actionField"]["tax"] = total_amount_tax_incl - total_amount_tax_excl;
				tagPurchase["ecommerce"]["purchase"]["actionField"]["fee"] = this.fees.fees_excl;
	  			let couponCode = null;

	  			if(this.discount_excl > 0){

	  				couponCode = this.coupon.code
					tagPurchase["ecommerce"]["purchase"]["actionField"]["coupon"] = couponCode

	  			}


				console.log ("DETECTION DU TRANSPORT")
				console.log ("Shipping = ", this.projectOrigin )
	  			let shippingKey = null; 

	  			if(Object.keys(this.shippingSelected).length > 0 && this.shippingSelected.constructor === Object){

	  				shippingKey = this.shippingSelected.key;
	  			
	  			} else {

					if (this.projectOrigin.type[0].shipping ) {shippingKey = 'maker_shipment';}
					else{shippingKey = 'not_shipped';}

	  				
	  			} 
					
				  
				let shippingPrice = 0;

	  			if(!this.shippingSelected.price){

	  				shippingPrice = 0;

	  			} else {
	  				shippingPrice = this.shippingSelected.price;
					tagPurchase["ecommerce"]["purchase"]["actionField"]["shipping"] = shippingPrice
	  			}

	  			let typeOrder = 'print';
	  			let quotationId = null;

	  			if(this.type == 'design'){

	  				typeOrder = 'design';
	  				quotationId = this.makerSelected.quotation.id;
	  				total_amount_tax_excl = this.makerSelected.price_excl;
	  				total_amount_tax_incl = this.makerSelected.price_incl;

	  				if(this.projectOrigin.shipping_required == false){

	  					let initAddress = {};
	  					initAddress.lastname = this.user3dm.address_shipping.lastname ;
				        initAddress.firstname = this.user3dm.address_shipping.firstname ;
				        initAddress.company = this.user3dm.address_shipping.company ;
				        initAddress.street1 = this.user3dm.address_shipping.street1 ;
				        initAddress.street2 = this.user3dm.address_shipping.street2 ;
				        initAddress.zipcode = this.user3dm.address_shipping.zipcode ;
				        initAddress.city = this.user3dm.address_shipping.city ;
				        initAddress.country = this.user3dm.address_shipping.country ;
				        initAddress.phone = this.user3dm.address_shipping.phone ;

				        store.commit('SAVE_ADDRESS_BILLING',initAddress);
						store.commit('SAVE_ADDRESS_SHIPPING',initAddress);

	  				}

	  			}
				if(this.type === 'basket') {
					var data = {
						"customer_id": this.user3dm.id, // id user (client final loggé)
						"type": 'model', // type is print or design
						"billing_address": {
							"lastname":  this.addressBilling.lastname,
							"firstname": this.addressBilling.firstname,
							"company":   this.addressBilling.company, // optionnel
							"street1":   this.addressBilling.street1,
							"street2":   this.addressBilling.street2, // optionnel
							"zipcode":   this.addressBilling.zipcode,
							"city":      this.addressBilling.city,
							"country":   this.addressBilling.country,
							"telephone": this.addressBilling.phone,
						},
						"coupon" : couponCode,
						"amounts": {
							// Corriger
							"total_amount_tax_incl": total_amount_tax_incl,      // montant total de la commande en entier (centimes)
							"total_amount_tax_excl": total_amount_tax_excl,
						},
						"id_basket": this.currentBasket[0].id_model
					}
				} else {
					var data = {
						"customer_id": this.user3dm.id, // id user (client final loggé)
						"maker_id": this.makerSelected.id,    // id maker
						"quotation_id": quotationId,
						"type": typeOrder, // type is print or design
						"billing_address": {
							"lastname":  this.addressBilling.lastname,
							"firstname": this.addressBilling.firstname,
							"company":   this.addressBilling.company, // optionnel
							"street1":   this.addressBilling.street1,
							"street2":   this.addressBilling.street2, // optionnel
							"zipcode":   this.addressBilling.zipcode,
							"city":      this.addressBilling.city,
							"country":   this.addressBilling.country,
							"telephone": this.addressBilling.phone,
						},
						"shipping_address": {
							"lastname":  this.addressShipping.lastname,
							"firstname": this.addressShipping.firstname,
							"company":   this.addressShipping.company, // optionnel
							"street1":   this.addressShipping.street1,
							"street2":   this.addressShipping.street2, // optionnel
							"zipcode":   this.addressShipping.zipcode,
							"city":      this.addressShipping.city,
							"country":   this.addressShipping.country,
							"telephone": this.addressShipping.phone,
						},
						"shipping_relay_identifier": this.addressShipping.identifier,
						"shipping_type": shippingKey,//this.shippingSelected.key, // valeur de la clé retournée dans l'api shipping (home_standard / home_express / relay / pickup)
						"coupon" : couponCode,
						"amounts": {
							// Corriger
							"total_amount_tax_incl": total_amount_tax_incl + this.fees.fees_incl + shippingPrice - this.discount_incl,      // montant total de la commande en entier (centimes)
							"total_amount_tax_excl": total_amount_tax_excl + this.fees.fees_excl + shippingPrice - this.discount_excl,
							"production_amount_tax_incl": total_amount_tax_incl, // montant de la production (objets et finitions)
							"production_amount_tax_excl": total_amount_tax_excl,
							"shipping_amount_tax_incl": shippingPrice,   // montant des frais de port
							"shipping_amount_tax_excl": shippingPrice,
							"fee_amount_tax_incl": this.fees.fees_incl,        // montant des frais de service
							"fee_amount_tax_excl": this.fees.fees_excl,
							"discount_amount_tax_incl": this.discount_incl,
							"discount_amount_tax_excl": this.discount_excl,
						},
						"instructions": this.instruction,
				    	"items": items
					}
				}
	  			

				console.log('DATA SEND FOR ORDER',data)
				
				
				if(this.type === 'basket') {
					console.log('API Payment Create => test1')
					//$("#dynamicBasket searchCategory").hide();
					
					var data = {
						"basket_id": this.currentBasket[0].id_model,
						"total_amount_tax_incl": total_amount_tax_incl,
						"total_amount_tax_excl": total_amount_tax_excl
					}
					console.log('API Order',data)
					this.$http.post(this.apiBasketorder, data ).then((findData) => 
					{
						console.log('API Order Create => success',findData)
						console.log('API Order CreateData => success',findData.body)
						var data = JSON.parse(findData.body);

						console.log('DATA response order', data.order_id)

						store.commit('UPDATE_ORDER_ID',data.order_id)
						store.commit('CHANGE_STEP',8)
						this.validateOrder(this.token)

					}, (findData) => {
						console.log('API Order Create => error',findData)
						/*
						if(this.type == "basket") {
							this.$http.post(this.apiPaymentcancel, data).then((findData) => 
							{
								console.log('API Order Modified => cancel')

							}, (findData) => {
								console.log('API Order Modified => error')
							})
						}
						*/
					})
				} else {
					this.$http.post(this.apiOrder, data ).then((response) => 
					{
						//console.log('API Order Create => success',response)
						var data = JSON.parse(response.body);

						//console.log('DATA response order', data.order_id)

						store.commit('UPDATE_ORDER_ID',data.order_id)
						tagPurchase["ecommerce"]["purchase"]["actionField"]["id"] = data.order_id

				
				
						//store.commit('CHANGE_STEP',8)
						this.validateOrder(this.token,paymentMethodId);
						// GTAG report purchase
						gtag_report_purchase (tagPurchase)
						// end GTAG
					}, (response) => {
						//console.log('API Order Create => error',response)

					})
				}
	  			
	  		},
		},
		watch:{
			paymentModeAuthorize : function(){

				if(this.user3dm.sepa === true){

					this.paymentMode = 'sepa'

				} else {

					this.paymentMode = 'card'

				}

			},
			checkboxCgv: function(){

				if(this.checkboxCgv == true){
					/*if(this.stripeInit == false){
						this.setUpStripe()
					}*/
					store.commit('TOGGLE_CONDITION_VALIDATE',true)
				} else {
					store.commit('TOGGLE_CONDITION_VALIDATE',false)
				}
			},
			stepFormProcess: function () {

				if(this.stepFormProcess == 8){

					if(this.stripeInit == false){
						this.setUpStripe()
					}

				}

		    },
		    stepFormProject: function () {

				if(this.stepFormProject == 8){

					if(this.stripeInit == false){
						this.setUpStripe()
					}

				}

		    },
		},
	}
</script>

<style>

</style>