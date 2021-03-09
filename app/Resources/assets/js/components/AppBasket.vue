<template>
	<div>
		
		<div class="row">
			<div id='js-scrollref' class="col-sm-9">
				<basket-file :api-coupon="urlApiCoupon" :api-basket="urlApiBasket" v-show="stepFormProcess < 9" :model-url="modelUrl" type="basket">
				</basket-file>

				<transition name="fade">
					<payment-file  :stripe-api-key="stripePublicKey" :api-order="urlApiOrder" :api-basketorder="urlApiBasketorder" :api-payment="urlApiPayment" :api-paymentcancel="urlApiPaymentCancel" v-show="stepFormProcess>=8" :email-contact="emailContact" :model-url="modelUrl" :command-url="commandUrl" :api-third-d-secure="urlApiThirdDSecure" type="basket">
					</payment-file>
				</transition>


			</div>

			<div class="col-sm-3" v-show="stepFormProcess<=8">
				<cart-file :api-fees="urlApiFees" type="basket">
				</cart-file>
			</div>
		</div>
	</div>
</template>

<script>

	import PrintFile from './PrintFile.vue'
	import MakerFile from './MakerFile.vue'
	import ShippingFile from './ShippingFile.vue'
	import AccountFile from './AccountFile.vue'
	import AddressFile from './AddressFile.vue'
	import CartFile from './CartFile.vue'
	import SummaryFile from './SummaryFile.vue'
	import BasketFile from './BasketFile.vue'
	import UploadFile from './UploadFile.vue'
	import PaymentFile from './PaymentFile.vue'
	import { mapGetters } from 'vuex'
	import store from '../stores/PrintStore'

	export default {
		store: store,
		components: { PrintFile,MakerFile,ShippingFile,AccountFile,AddressFile,CartFile,BasketFile,SummaryFile,UploadFile,PaymentFile},
		data: function(){
		  	return {
				
		  	}
	  	},
	  	computed: {
	  		...mapGetters([
				'print3dFiles',
				'makersList',
				'stepFormProcess',
				'makerSelected',
				'shippingSelected',
				'uploadProcess',
				'orderId',
				'addressBilling',
				'addressShipping',
				'user3dm',
				'currentBasket',
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
					material: null,
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
	  			setTimeout(function() {$('html,body').animate({scrollTop: $('#makers').offset().top},'slow');}, 200);
				
	  		},
	  	},
	  	mounted(){
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
			this.urlApiBasket = this.$el.attributes['url-api-basket'].value
			this.urlApiBasketorder = this.$el.attributes['url-api-basketOrder'].value
			this.urlApiPayment = this.$el.attributes['url-api-payment'].value
			this.urlApiPaymentCancel = this.$el.attributes['url-api-payment-cancel'].value
			this.stripePublicKey = this.$el.attributes['stripe-public-key'].value
			this.urlApiCoupon = this.$el.attributes['url-api-coupon'].value
			this.emailContact = this.$el.attributes['email-contact'].value
			this.modelUrl = this.$el.attributes['url-model'].value
			this.commandUrl = this.$el.attributes['url-command'].value
			this.urlApiThirdDSecure = this.$el.attributes['url-api-third-d-secure'].value
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