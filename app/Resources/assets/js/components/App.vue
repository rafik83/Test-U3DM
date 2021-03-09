<template>
	<div>
		
		<div class="row">
			<div id='js-scrollref' class="col-sm-9">



				<div id="product" class="col-sm-12 bg-white pad-15" v-show="stepFormProcess < 9">

					<h2><span class="rounded">1</span> Mes fichiers 3D</h2>

					<print-file  v-for="file in print3dFiles" :product-id="file.fileNumber" :api-pricing="urlApiPricing" :key="file.printFileNumber" v-if="stepFormProcess>=1 && stepFormProcess < 9">
					</print-file>

				</div>

				<div class="text-center col-sm-12 mrg-t-20" v-show="print3dFiles.length==0">
					<button v-on:click="addFile" class="btn btn-default btn-rounded btn-grey"> + Ajouter un fichier</button>
				</div>
				<div class="text-center col-sm-12 mrg-t-20" v-show="lockedAllFile && stepFormProcess < 9">
					<button v-on:click="addFile" class="btn btn-default btn-rounded btn-grey" v-show="stepFormProcess<=2"> + Ajouter un fichier</button>
					<button v-on:click="chooseMaker" class="btn btn-default btn-rounded"> > Choisir mon Maker</button>
				</div>

				<transition name="fade">
					<maker-file  :api-fees="urlApiFees" v-show="stepFormProcess>=2 && lockedAllFile && stepFormProcess < 9">
					</maker-file>
				</transition>

				<transition name="fade">
					<shipping-file  :api-shipping="urlApiShipping" v-show="stepFormProcess>=3 && stepFormProcess < 9" type="print">
					</shipping-file>
				</transition>

				<transition name="fade">
					<account-file  :api-user-create="urlApiUserCreate" :api-user-login="urlApiUserLogin" :api-user-connected="urlApiUserConnected" :api-user-logout="urlApiUserLogout" v-show="stepFormProcess>=4 && stepFormProcess < 9" type="print">
					</account-file>
				</transition>

				<transition name="fade">
					<address-file  v-show="stepFormProcess>=5 && stepFormProcess < 9" :api-chronopost="urlApiChronopost" type="print">
					</address-file>
				</transition>

				<transition name="fade">
					<summary-file :api-coupon="urlApiCoupon" v-show="stepFormProcess>=6 && stepFormProcess < 9" type="print">
					</summary-file>
				</transition>

				<transition name="fade">
					<upload-file  :api-upload="urlApiUpload" v-show="stepFormProcess>=7 && stepFormProcess < 9">
					</upload-file>
				</transition>

				<transition name="fade">
					<payment-file  :stripe-api-key="stripePublicKey" :api-order="urlApiOrder" :api-payment="urlApiPayment" :api-third-d-secure="urlApiThirdDSecure" v-show="stepFormProcess>=8" :email-contact="emailContact" type="print">
					</payment-file>
				</transition>


			</div>

			<div class="col-sm-3" v-show="stepFormProcess<=8">
				<cart-file :api-fees="urlApiFees">
				</cart-file>
			</div>
		</div>
	</div>
</template>

<script>
	import AccountFile from './AccountFile.vue'
	import PrintFile from './PrintFile.vue'
	import MakerFile from './MakerFile.vue'
	import ShippingFile from './ShippingFile.vue'
	
	import AddressFile from './AddressFile.vue'
	import CartFile from './CartFile.vue'
	import SummaryFile from './SummaryFile.vue'
	import UploadFile from './UploadFile.vue'
	import PaymentFile from './PaymentFile.vue'
	import { mapGetters } from 'vuex'
	import store from '../stores/PrintStore'

	export default {
		store: store,
		components: { AccountFile,PrintFile,MakerFile,ShippingFile,AddressFile,CartFile,SummaryFile,UploadFile,PaymentFile},
		data: function(){
		  	return {
				
		  	}
	  	},
	  	computed: {
	  		...mapGetters([
				'user3dm',
				'print3dFiles',
				'makersList',
				'stepFormProcess',
				'makerSelected',
				'shippingSelected',
				'uploadProcess',
				'orderId',
				'addressBilling',
				'addressShipping',
				
				'fees',
				'instruction',
				'discount_excl',
				'discount_incl',
				'coupon',
			]),
			lockedAllFile : function(){

				let lockState = false

				for (const key in this.print3dFiles){

					if(this.print3dFiles[key].state != 'open'){

						lockState = true

					} else {

						lockState = false

						break
					}

				}

				return lockState
			}
			
	  	},
	  	methods: {
	  		addFile(){
	  			let model = {
					fileNumber: 0,
					file: null,
					filename: null,
					fileIdDb: null,
					extension: null,
					dimensions: {
						x: null,
						y: null,
						z: null
					},
					volume: null,
					weight: null,
					density: null,
					color: '#760039',
					finishing: [],
					layer: null,
					technology: null,
					technologyLabel: null,
					material: null,
					materialLabel: null,
					filling: null,
					heightPrinting: null,
					quantity: 0,
					numberOfParts: null,
					makersList: [],
					priceTaxEcl: 0,
					priceTaxEclDisplay : 0,
					priceTaxEclUnit: 0,
					priceTaxEclUnitDisplay : 0,
					priceTaxInc: 0,
					priceTaxIncUnit: 0,
					uploaded:0,
					state: 'open', // open / lock 
				}
	  			store.commit('ADD_PRINT_3DFILE',model)
	  			store.commit('CHANGE_STEP',1)
	  		},
	  		chooseMaker(){
	  			store.commit('CHANGE_STEP',2)
				// Google Tag Manager : push event Maker View
				//******************************************** */
				gtag_report_event(this.user3dm,'impression_form','impression_form.makers.view')
				//******************************************** */
	  			setTimeout(function() {$('html,body').animate({scrollTop: $('#makers').offset().top},'slow');}, 200);
				
	  		},
	  	},
	  	mounted(){
			console.log("App.vue")
	  		this.addFile()
	  	},
	  	beforeMount: function() {
			this.urlApiPricing = this.$el.attributes['url-api-pricing'].value
			this.urlApiShipping = this.$el.attributes['url-api-shipping'].value
			this.urlApiFees = this.$el.attributes['url-api-fees'].value
			this.urlApiUserCreate = this.$el.attributes['url-api-user-create'].value
			this.urlApiUserLogin = this.$el.attributes['url-api-user-login'].value
			this.urlApiUserLogout = this.$el.attributes['url-api-user-logout'].value
			this.urlApiUserConnected = this.$el.attributes['url-api-user-connected'].value
			this.urlApiChronopost = this.$el.attributes['url-api-chronopost'].value
			this.urlApiUpload = this.$el.attributes['url-api-upload'].value
			this.urlApiOrder = this.$el.attributes['url-api-order'].value
			this.urlApiPayment = this.$el.attributes['url-api-payment'].value
			this.urlApiThirdDSecure = this.$el.attributes['url-api-third-d-secure'].value
			this.stripePublicKey = this.$el.attributes['stripe-public-key'].value
			this.urlApiCoupon = this.$el.attributes['url-api-coupon'].value
			this.emailContact = this.$el.attributes['email-contact'].value


		},
    	watch: {

		    stepFormProcess: function(val) {

		    	if(val < 6 && this.discount_excl > 0){

		    		store.commit('REMOVE_COUPON')

		    	}

		    }
		}
	}


</script>

<style scoped>

</style>