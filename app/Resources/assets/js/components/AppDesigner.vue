<template>
	<div>
		<div class="row">
			<div id='js-scrollref' :class="{'col-sm-12' : widthSmall , 'col-sm-9': widthWide}">

				<!-- Message for client -->
				<transition name="fade">
					<div id="client-message" class="col-sm-12 bg-white pad-15 mrg-t-20 txt-modelisation-client" v-if="displayClientMessage" style="margin-bottom:40px;">
						<div class="col-sm-10 text-center">
							<p v-html="clientMessage"></p>
						</div>
						<div class="col-sm-2 text-center">
							<a :href="urlUserProjectList"> {{label1}}</a>
						</div>
					</div>
				</transition>

				<div id="product" class="col-sm-12 bg-white pad-15" v-show="stepFormProject < 9">

					<h2><span class="rounded">1</span> {{label1}}</h2>
					<project-design :api-url-referentiel-design="apiUrlReferentielDesign" :state-project="stateProject" :api-url-delete-file="apiUrlDeleteFile">
					</project-design>

				</div>

				<account-file  :api-user-create="urlApiUserCreate" :api-user-login="urlApiUserLogin" :api-user-connected="urlApiUserConnected" :api-user-logout="urlApiUserLogout" type="design" v-show="stepFormProject > 1 && isConnected == 0">
				</account-file>

				<save-project-design v-show="stateProject == 'open' && stepFormProject > 1 && isConnected" :url-api-send-project="urlApiSendProject"></save-project-design>

                <div class="col-sm-12 bg-white pad-15 mrg-t-40" v-show="stateProject == 'open' && stepFormProject <= 1">
                    <div class="row mrg-20-0">
                        <div class="col-sm-12 text-center">
                            <p style="margin-bottom: 18px">Veuillez remplir tous les champs obligatoires pour pouvoir valider votre projet.</p>
                        </div>
                    </div>
                </div>

				<transition name="fade">
					<maker-file  :api-fees="urlApiFees" type="design" v-show="displayMaker && stepFormProject < 9">
					</maker-file>
				</transition>	

				<transition name="fade">
					<shipping-file  :api-shipping="urlApiShipping" type="design" v-show="stepFormProject>=3 && stepFormProject < 9 && displayMaker & shippingRequired == true">
					</shipping-file>
				</transition>

				<transition name="fade">
					<address-file  v-show="stepFormProject>=5 && stepFormProject < 9 & shippingRequired == true" type="design" :api-chronopost="urlApiChronopost">
					</address-file>
				</transition>

				<transition name="fade">
					<summary-file :api-coupon="urlApiCoupon" type="design" v-if="stepFormProject>=6 && stepFormProject < 9" :shipping-required="shippingRequired">
					</summary-file>
				</transition>

				<transition name="fade">
					<payment-file  :stripe-api-key="stripePublicKey" :api-order="urlApiOrder" :api-payment="urlApiPayment" :api-third-d-secure="urlApiThirdDSecure" v-show="stepFormProject>=8 && stepFormProject < 10" :email-contact="emailContact" :url-new-project="urlNewProject" type="design" :shipping-required="shippingRequired">
					</payment-file>
				</transition>

				<!-- Message for client -->
				<!-- <transition name="fade">
					<div id="client-message" class="col-sm-12 bg-white pad-15 mrg-t-40" v-if="displayClientMessage">
						<div class="col-sm-12 text-center">
							
							<p v-html="clientMessage"></p>

							<p>L'équipe United 3D Makers.</p>

						</div>
					</div>
				</transition> -->

			</div>

			<div class="col-sm-3" v-show="widthWide">
				<cart-file :api-fees="urlApiFees" type="design">
				</cart-file>
			</div>
		</div>
	</div>
</template>

<script>

	import ProjectDesign from './ProjectDesign.vue'
	import MakerFile from './MakerFile.vue'
	import CartFile from './CartFile.vue'
	import ShippingFile from './ShippingFile.vue'
	import AddressFile from './AddressFile.vue'
	import AccountFile from './AccountFile.vue'
	import SummaryFile from './SummaryFile.vue'
	import PaymentFile from './PaymentFile.vue'
	import SaveProjectDesign from './SaveProjectDesign.vue'
	import { mapGetters } from 'vuex'
	import store from '../stores/PrintStore'

	export default {
		store: store,
		props: [
			'tagSpecial'],
		components: { ProjectDesign, AccountFile, SaveProjectDesign, MakerFile, CartFile, ShippingFile, AddressFile, SummaryFile, PaymentFile },
		data: function(){
		  	return {
		  		isConnected : 0,
		  		widthSmall: true,
		  		widthWide: false,
		  		displayMaker: false,
		  		originalProject: {},
		  		originalMakers: {},
		  		displayClientMessage: false,
		  		clientMessage:null,
		  		shippingRequired: false,
				expiredValidity: false,

				label1:'',
		  	}
	  	},
	  	computed: {
	  		...mapGetters([
				'stepFormProject',
				'user3dm',
			]),
	  	},
	  	methods: {
	  		
	  	},
	  	mounted(){
			

	  		//console.log('originalProject',this.originalProject)
	  		
	  	},
	  	beforeMount: function() {

	  		this.apiUrlReferentielDesign = this.$el.attributes['api-url-referentiel-design'].value
	  		this.apiUrlDeleteFile = this.$el.attributes['api-url-delete-file'].value
	  		this.urlApiFees = this.$el.attributes['url-api-fees'].value
	  		this.urlApiShipping = this.$el.attributes['url-api-shipping'].value
			this.stripePublicKey = this.$el.attributes['stripe-public-key'].value
			this.emailContact = this.$el.attributes['email-contact'].value
			this.urlApiChronopost = this.$el.attributes['url-api-chronopost'].value
			this.stateProject = this.$el.attributes['state-project'].value
			this.urlApiUserCreate = this.$el.attributes['url-api-user-create'].value
			this.urlApiUserLogin = this.$el.attributes['url-api-user-login'].value
			this.urlApiUserLogout = this.$el.attributes['url-api-user-logout'].value
			this.urlApiUserConnected = this.$el.attributes['url-api-user-connected'].value
			this.urlApiSendProject = this.$el.attributes['url-api-send-project'].value
			this.urlApiCoupon = this.$el.attributes['url-api-coupon'].value
			this.urlApiOrder = this.$el.attributes['url-api-order'].value
			this.urlApiPayment = this.$el.attributes['url-api-payment'].value
			this.urlApiThirdDSecure = this.$el.attributes['url-api-third-d-secure'].value
			this.urlNewProject = this.$el.attributes['url-new-project'].value
			this.urlCovidProject = this.$el.attributes['url-covid-project'].value
			this.urlUserProjectList = this.$el.attributes['url-user-project-list'].value

			gtag_report_event(this.user3dm,'project_form','project_form.view')

			this.label1='Mon Projet'
			if (tagSpecial == "COVID") {
				this.label1='Ensemble luttons contre le coronavirus'
				this.urlNewProject = this.$el.attributes['url-covid-project'].value
				}

			store.commit('SET_URL_NEW_PROJECT',this.urlNewProject);

			//Get object project form twig
			if(projectOrigin != null){
				this.originalProject = JSON.parse(projectOrigin);

				if(this.originalProject.shipping_required == true){
					this.shippingRequired = true;
				}

			} else {
				this.originalProject = {}
			}
			

			if(makers != null){
				this.originalMakers = JSON.parse(makers);
			} else {
				this.originalMakers = {}
			}

			this.expiredValidity = expiredValidityDate;

			if(this.expiredValidity == "1"){

				store.commit('SET_EXPIRED_DATE_VALIDITY',true);

			} else {

				store.commit('SET_EXPIRED_DATE_VALIDITY',false);

			}

			/*if(this.expiredValidity == "1"){
				this.displayClientMessage = true;
			    this.clientMessage = 'Période de validité des devis dépassée. <br>Vous pouvez contacter United-3d-Makers.';
			}*/
			

			if(Object.keys(this.originalProject).length > 0 && this.originalProject.constructor === Object){

				store.commit('FILL_ORIGIN_PROJECT',this.originalProject);

				//Message for client
				// Constant backend
				/*
				const STATUS_CREATED    = 1;
			    const STATUS_SENT       = 2;
			    const STATUS_DISPATCHED = 3;
			    const STATUS_CLOSED     = 4;
			    const STATUS_DELETED    = 5;
			    const STATUS_ORDERED    = 6;
			    */
			    if(this.originalProject.status == 2){

			    	this.displayClientMessage = true;
			    	this.clientMessage = 'Votre demande a été transmise aux makers, vous recevrez bientôt des devis';

			    } else if(this.originalProject.status == 3 && this.expiredValidity != true && makers == null ){

			    	this.displayClientMessage = true;
			    	this.clientMessage = 'Votre demande a été transmise aux makers, vous recevrez bientôt des devis';

			    } else if(this.originalProject.status == 3 && this.expiredValidity != true && makers != null ){

			    	this.displayClientMessage = true;
			    	this.clientMessage = 'Choisissez votre maker';

			    } else if(this.originalProject.status == 3 && this.expiredValidity == true ){

			    	this.displayClientMessage = true;
			    	this.clientMessage = 'La date de validité des devis est dépassée. <br> Pour la prolonger, contactez <a href="mailto:'+this.emailContact+'"> l\'équipe </a>';

			    } else if(this.originalProject.status == 4){

			    	this.displayClientMessage = true;
			    	this.clientMessage = 'Ce projet a été clôturé';

			    } else if(this.originalProject.status == 5){

			    	this.displayClientMessage = true;
			    	this.clientMessage = 'Ce projet a été supprimé :<br>' + this.originalProject.deletion_reason;

			    } else if(this.originalProject.status == 6){

			    	this.displayClientMessage = true;
			    	this.clientMessage = 'Ce projet a fait l\'objet d\'une commande';
			    	if (null !== this.originalProject.order_url) {
			    	    this.clientMessage += '<br><a href="'+ this.originalProject.order_url +'">Voir la commande</a>';
                    }
                    if (null !== this.originalProject.quotation_url) {
			    	    this.clientMessage += '<br><a href="'+ this.originalProject.quotation_url +'">Voir le devis</a>';
                    }

			    } else if(this.originalProject.status == 1 && this.originalProject.return_reason != null){

                    this.displayClientMessage = true;
                    this.clientMessage = 'Ce projet a été retourné pour modification :<br>' + this.originalProject.return_reason;

                }

			}

			//console.log('Maker in vue',this.makers)

			if(Object.keys(this.originalMakers).length > 0 && this.originalMakers.constructor === Object /*&& this.expiredValidity == false*/){
			//if(this.makers != 'null'){

				this.widthSmall = false;
		  		this.widthWide = true;
		  		this.displayMaker = true;

				console.log('Makers',this.originalMakers);

				store.commit('MAKE_MAKER_LIST',this.originalMakers);

			}

		},
    	watch: {
		    user3dm: {
		    	handler(obj){

		    		if (Object.keys(obj).length === 0 && obj.constructor === Object){

		    			this.isConnected = 0

					} else {

						this.isConnected = 1
					}

			     	},
			    deep: true
		  	},
		}
	}


</script>

<style scoped>

</style>