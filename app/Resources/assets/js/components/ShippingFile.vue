<template>
	<div>
		<div id="shipping" class="col-sm-12 bg-white pad-15 mrg-t-20">
            <h2><span class="rounded">3</span> Choisissez votre mode de livraison</h2>
            <div class="flex text-center">
            	<label for="radio_company_1" v-if="shippingReference.home_standard>0">
                    <i class="fas fa-truck fa-5x mrg-15-0"></i>
            		<p class="mrg-0 h3">Standard</p>
            		<div class="radio mrg-0">
            			<label>
	            			<input type="radio" name="shippingMode" id="radio_company_1" value="home_standard" v-model="shippingMode"/>
	            			<span><!-- fake radio --></span>
	            			<span class="wrapped-label h4">{{shippingReference.home_standard/100}} €</span>
            			</label>
            		</div>
            		<p class="txt-default thin">Livraison contre signature <br> 2-3 jours après expédition</p>
            	</label>
            	<label for="radio_company_2" v-if="shippingReference.home_express>0">
                    <i class="fas fa-shipping-fast fa-5x mrg-15-0"></i>
            		<p class="mrg-0 h3">Express</p>
            		<div class="radio mrg-0">
            			<label>
	            			<input type="radio" name="shippingMode" id="radio_company_2" value="home_express" v-model="shippingMode"/>
	            			<span></span>
	            			<span class="wrapped-label h4">{{shippingReference.home_express/100}} €</span>
            			</label>
            		</div>
            		<p class="txt-default thin">J+1 avant 13h, après expédition</p>
            	</label>
            	<label for="radio_company_3" v-if="shippingReference.relay>0">
                    <i class="fas fa-map-marker fa-5x mrg-15-0"></i>
            		<p class="mrg-0 h3">Relais</p>
            		<div class="radio mrg-0">
            			<label>
	            			<input type="radio" name="shippingMode" id="radio_company_3" value="relay" v-model="shippingMode"/>
	            			<span></span>
	            			<span class="wrapped-label h4">{{shippingReference.relay/100}} €</span>
            			</label>
            		</div>
            		<p class="txt-default thin">2-3 jours après expédition</p>
            	</label>
            	<label for="radio_company_4" v-if="pickupAvailable && type != 'design'">
                    <i class="fas fa-people-carry fa-5x mrg-15-0"></i>
            		<p class="mrg-0 h3">Sur place</p>
            		<div class="radio mrg-0">
            			<label>
	            			<input type="radio" name="shippingMode" id="radio_company_3" value="pickup" v-model="shippingMode"/>
	            			<span></span>
	            			<span class="wrapped-label h4">Gratuit</span>
            			</label>
            		</div>
            		<p class="txt-default thin">À retirer chez le maker</p>
            	</label>
            	<!-- ////////////////////////// -->
            	<!-- ////////////////////////// -->
            	<!-- ////////////////////////// -->
            	<!-- ////////////////////////// -->
            	<!-- Guillaume => DEV OK Shipping -->
            	<!-- ////////////////////////// -->
            	<!-- ////////////////////////// -->
            	<!-- ////////////////////////// -->
            	<!-- ////////////////////////// -->
            	<!-- <label for="radio_company_3" v-if="shippingReference.relay>0">
                    <i class="fas fa-map-marker fa-5x mrg-15-0"></i>
            		<p class="mrg-0 h3">Relais</p>
            		<div class="radio mrg-0">
            			<label>
	            			<input type="radio" name="shippingMode" id="radio_company_3" value="relay" v-model="shippingMode"/>
	            			<span></span>
	            			<span class="wrapped-label h4">{{shippingReference.relay/100}} €</span>
            			</label>
            		</div>
            		<p class="txt-default thin">2-3 jours après expédition</p>
            	</label>
            	<label for="radio_company_4" v-if="pickupAvailable">
                    <i class="fas fa-people-carry fa-5x mrg-15-0"></i>
            		<p class="mrg-0 h3">Sur place</p>
            		<div class="radio mrg-0">
            			<label>
	            			<input type="radio" name="shippingMode" id="radio_company_4" value="pickup" v-model="shippingMode"/>
	            			<span></span>
	            			<span class="wrapped-label h4">Gratuit</span>
            			</label>
            		</div>
            		<p class="txt-default thin">À retirer chez le maker</p>
            	</label> -->
            </div>
    </div>
    </div>
</template>


<script>
	
	import Vue from 'vue'
	import { mapGetters } from 'vuex'
	import store from '../stores/PrintStore'


	export default {
		name: "shippingFile",
		store: store,
		props: [
			'apiShipping',
			'productId',
			'type',
		],
		data: function(){
			return {
				shippingMode:null,
				shippingReference:{},
				pickupAvailable:false,
				pickupAddress:false,
			}
		},
		mounted (){

			//this.apiFindShipping()

		},
		computed: {
			...mapGetters([
				'print3dFiles',
				'makersList',
				'makerSelected',
				'user3dm',
				'stepFormProcess',
			]),
		},
		methods: {
			apiFindShipping(){

				let data = {'makerId': this.makerSelected.id }
				
				this.$http.post(this.apiShipping, data ).then((response) => 
				{
					console.log('API Shipping => success')


					var data = JSON.parse(response.body)

					this.shippingReference = data 
					this.pickupAvailable = data.pickup.available
					this.pickupAddress = data.pickup.address

					//this.shippingReference.pickup.available = data.pickup.available

				}, (response) => {

					console.log('API Shipping => error',response)

				})

			},
		},
		watch:{
			stepFormProcess: function(){

				if(this.stepFormProcess == 3){

					this.shippingMode = null
					
				}

			},
			shippingMode: function(){

				let shippingObj = {}

				console.log(this.shippingMode)

				if(this.shippingMode == 'home_standard'){

					shippingObj = {
						'key' : 'home_standard',
						'name' : 'Livraison Standard',
						'price' : this.shippingReference.home_standard ,
						'address' : null,
					}

				} else if(this.shippingMode == 'home_express'){

					shippingObj = {
						'key' : 'home_express',
						'name' : 'Livraison Express',
						'price' : this.shippingReference.home_express ,
						'address' : null,
					}

				} else if(this.shippingMode == 'relay'){

					shippingObj = {
						'key' : 'relay',
						'name' : 'Livraison en point relais',
						'price' : this.shippingReference.relay ,
						'address' : null,
					}

				} else if(this.shippingMode == 'pickup'){

					shippingObj = {
						'key' : 'pickup',
						'name' : 'À retirer sur place',
						'price' : 0,
						'address' : this.pickupAddress,
					}

				}

				if(this.shippingMode != null){
					store.commit('ADD_SHIPPING', shippingObj)



					// If user connected jump to address
					if(Object.keys(this.user3dm).length === 0 && this.user3dm.constructor === Object){
						store.commit('CHANGE_STEP',4)
						setTimeout(function() {$('html,body').animate({scrollTop: $('#account').offset().top},'slow')}, 200)

						// user is not connected - as this step it is possible only on type = 'print'
						// Google Tag Manager : push event delivery Selected
						//******************************************** */
						gtag_report_event(this.user3dm,'impression_form','impression_form.delivery.selected')
						//******************************************** */

					} else {

						if(this.type == 'design'){

							store.commit('CHANGE_STEP_PROJECT',5)
							// Google Tag Manager : push event delivery Selected
							//******************************************** */
							gtag_report_event(this.user3dm,'project_form','project_form.delivery.selected')
							//******************************************** */

						} else {

							store.commit('CHANGE_STEP',5)
							// Google Tag Manager : push event delivery Selected
							//******************************************** */
							gtag_report_event(this.user3dm,'impression_form','impression_form.delivery.selected')
							//******************************************** */


						}

						setTimeout(function() {$('html,body').animate({scrollTop: $('#address').offset().top},'slow')}, 200)
						
					}
				}

			},
			makerSelected: function(val){

				if(Object.keys(val).length  > 0){

					this.apiFindShipping()

				}

			}
		}
	}
</script>

<style>

</style>