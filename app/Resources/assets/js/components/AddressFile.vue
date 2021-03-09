<template>
	<div>
		<div id="address" class="col-sm-12 bg-white pad-15 mrg-t-40">
            <h2 v-show="shippingSelected.key != 'relay' && shippingSelected.key != 'pickup'"><span class="rounded">4</span> Choisissez votre adresse de livraison</h2>
            <h2 v-show="shippingSelected.key == 'relay'"><span class="rounded">4</span> Choisissez votre point relais</h2>
            <h2 v-show="shippingSelected.key == 'pickup'"><span class="rounded">4</span> Retrait à l'adresse suivante</h2>
            <transition name="fade">
	    		<div class="col-sm-12 alert alert-danger" role="alert" v-if="errorFormShipping">
					{{errorFormShipping}}
	    		</div>
	    	</transition>

	    	<div class="col-sm-12" v-show="shippingSelected.key != 'pickup' && shippingSelected.key != 'relay' && user3dm.address_billing ">
	    		<div class="row">
	    			<div class="col-sm-12" v-show="displayField == 1">
	    				<label>
					        <input type="checkbox" name="sameAddress" v-model="sameAddress" value="true">
					        <span></span>
					        <span class="wrapped-label">Même adresse que facturation</span>
					    </label>
	    			</div>
	    		</div>
			</div>
			<div class="col-sm-12" v-show="shippingSelected.key == 'home_standard' || shippingSelected.key == 'home_express'">
		    	<div class="row">
		    		<div class="col-sm-12"><h3>Adresse de Livraison : </h3></div>
					<div class="col-sm-6">
						<input type="text" v-model="shippingFirstname" id="shipping-firstname" class="form-control" placeholder="Prénom*" v-if="displayField == 1">
						<span v-else>{{shippingFirstname}} {{shippingLastname}}</span>
					</div>
					<div class="col-sm-6">
						<input type="text" v-model="shippingLastname" id="shipping-lastname" class="form-control" placeholder="Nom*" v-if="displayField == 1">
					</div>
					<div class="col-sm-6">
						<input type="text" v-model="shippingCompany" id="shipping-company" class="form-control" placeholder="Société" v-if="displayField == 1">
						<span v-else>{{shippingCompany}} {{shippingPhone}} </span>
					</div>
					<div class="col-sm-6">
						<input type="text" v-model="shippingPhone" id="shipping-phone" class="form-control" placeholder="Téléphone*" v-if="displayField == 1">
					</div>
				</div>
				<div class="row">
				    <div class="col-sm-6">
						<input type="text" v-model="shippingAddress1" id="shipping-address1" class="form-control" placeholder="Adresse*" v-if="displayField == 1">
						<span v-else>{{shippingAddress1}}</span>
				    </div>
					<div class="col-sm-6">
						<input type="text" v-model="shippingAddress2" id="shipping-address2" class="form-control" placeholder="Complément d'adresse" v-if="displayField == 1">
						<span v-else>{{shippingAddress2}}</span>
				    </div>
				</div>
				<div class="row">
				    <div class="col-sm-3">
						<input type="text" v-model="shippingZipcode" id="shipping-zipcode" class="form-control" placeholder="Code Postal*" v-if="displayField == 1">
						<span v-else>{{shippingZipcode}} - {{shippingCity}} - {{shippingCountry}}</span>
				    </div>
					<div class="col-sm-4">
						<input type="text" v-model="shippingCity" id="shipping-city" class="form-control" placeholder="Ville*" v-if="displayField == 1">
				    </div>
				    <div class="col-sm-5">
					    	<select v-model="shippingCountry" v-if="displayField == 1">
					    		<option value="FR" selected="selected">France</option>
					    		<option value="BE">Belgique</option>
					    	</select>
					</div>
					<div class=" text-right col-sm-12">		    		  	
						<button class="btn btn-default btn-rounded" @click="validateAddress" v-if="displayField == 1">Confirmer adresse</button>
						<button class="btn btn-default btn-rounded btn-grey" @click="updateAddress" v-else>Modifier l'adresse</button>
					</div>
				</div>
			</div>
			<div v-show="this.shippingSelected.key == 'relay'">
				<div class="row">
					<div class="col-sm-12">
						Point relais à proximité de : 
						<strong>{{relayStreet}}, {{relayZipcode}} - {{relayCity}}</strong> 
						<a @click="changeAddressReference=1" v-show="changeAddressReference == 0">Changer d'adresse</a>
						<a @click="changeAddressReference=0" v-show="changeAddressReference == 1">Annuler</a>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12" v-show="changeAddressReference">
					    <div class="col-sm-4">
							<input type="text" v-model="newRelayStreet" class="form-control" placeholder="Adresse">
					    </div>
						<div class="col-sm-2">
							<input type="text" v-model="newRelayZipcode" class="form-control" placeholder="Code Postal">
					    </div>
					    <div class="col-sm-2">
							<input type="text" v-model="newRelayCity" class="form-control" placeholder="Ville">
					    </div>
					    <div class="col-sm-2">
							<button class="btn btn-default btn-rounded" @click="modifyRelayAddress">Modifier</button>
					    </div>
					</div>
						<!-- <vue-google-autocomplete
				            ref="address"
				            id="map"
				            classname="form-control"
				            placeholder="Merci de saisir une adresse"
				            v-on:placechanged="getAddressData"
				            country="fr"
				        >
				        </vue-google-autocomplete> -->

				</div>
				<div class="row" style="padding-top:15px;" v-show="mapDisplay">
					<div class="col-sm-4" style="padding-right:0px;">
						<div class="col-sm-12" id="container-relay">
							<div v-for="(relay, indexRelay) in listRelay" v-bind:value="relay.identifier" class="list-relay" @mouseover="hoverMarker(indexRelay)" @mouseleave="hoverMarkerOut(indexRelay)">
							<p>
								<div class="number-relay">{{relay.number}}</div>
								<strong>{{relay.name}}</strong>
							</p>
							<p v-for="opening in relay.opening" style="font-size:10px;">
								{{opening}}
							</p>
							<p>{{relay.street1}} {{relay.street2}}</p>
							<p>{{relay.zipcode}} - {{relay.city}}</p>
							<p style="color:#960048;"><strong>+- {{relay.distance/1000}} km</strong></p>
							<button class="btn btn-default btn-rounded" @click="selectRelay({'id':relay.identifier,'street1': relay.street1,'street2': relay.street2, 'zipcode':relay.zipcode, 'city':relay.city, 'name': relay.name})">Sélectionner</button>
							<hr>
							</div>
						</div>
					</div>
					<div class="col-sm-8">
						<GmapMap class="col-sm-12" style="height:303px;width:100%" :zoom="2" :center="{lat: 0, lng: 0}"
					        ref="map" map-type-id="roadmap" >
					        <GmapMarker v-for="(markerRelay, indexRelay) in listRelay"
					        :key="indexRelay"
					        :icon="iconRelay"
					        ref="markers"
					        :position="{'lat': markerRelay.coordinates.lat, 'lng': markerRelay.coordinates.lng}"
					        :label="{'text' : markerRelay.number,'font-weight':'bold', 'color':'white'}"
					        >
					    	</GmapMarker>
					    </GmapMap>
					</div>
				</div>
				<div class="row" style="padding:15px;" v-show="!mapDisplay">
					<div class="col-sm-12 relay-not-found">
						<p>
							Aucun point relais à proximité de cette adresse, merci de modifier l'adresse. <a @click="changeAddressReference=1" v-show="changeAddressReference == 0">Changer d'adresse</a>
						</p>
					</div>
				</div>
			</div>

			<div class="col-sm-12" v-show="this.shippingSelected.key == 'pickup'">
				<div class="row">
					<!-- <div class="col-sm-12"><h3>Retrait à l'adresse ci-dessous : </h3></div> -->
					<div class="col-sm-12">
						<span class="txt-purple">{{shippingCompany}}</span> 
						<span style="color:black;">{{shippingZipcode}} {{shippingCity}} {{shippingCountry}}</span> 
						<span v-show="makerSelected.distance > 0">
							à {{makerSelected.distance / 1000 }} Km de votre adresse 
							<span v-if="user_pickup_address">: {{ user_pickup_address }}</span>
						</span>
					</div>
					<!-- <div class="col-sm-12 txt-purple" v-show="makerSelected.distance > 0">
						+- {{makerSelected.distance / 1000 }} Km
					</div> -->
					<div class="col-sm-12">
						L'adresse complète vous sera transmis par email après la commande
					</div>
				
					<!-- <div class="col-sm-6">
						{{shippingAddress1}} {{shippingAddress2}}
					</div> -->
			
					<!-- <div class="col-sm-8">
						{{shippingZipcode}} {{shippingCity}} {{shippingCountry}}
					</div> -->
					<div class=" text-right col-sm-12">		    		  	
						<button class="btn btn-default btn-rounded" @click="validateAddress">Confirmer adresse</button>
					</div>
				</div>
			</div>

    	</div>
    </div>
</template>


<script>
	
	import Vue from 'vue'
	import { mapGetters } from 'vuex'
	import store from '../stores/PrintStore'
	/*import VueGoogleAutocomplete from 'vue-google-autocomplete'*/


	export default {
		name: "addressFile",
		components: {/*VueGoogleAutocomplete*/},
		store: store,
		props: [
			'apiChronopost',
			'productId',
			'type',
		],
		data: function(){
			return {
			
				errorFormShipping:null,
				gtagGroupEvent:'',
				shippingFirstname:null,
				shippingLastname:null,
				shippingCompany:null,
				shippingPhone:null,
				shippingAddress1:null,
				shippingAddress2:null,
				shippingZipcode:null,
				shippingCity:null,
				shippingCountry:'FR',

				billingFirstname:null,
				billingLastname:null,
				billingCompany:null,
				billingPhone:null,
				billingAddress1:null,
				billingAddress2:null,
				billingZipcode:null,
				billingCity:null,
				billingCountry:null,

				/*mapDisplay:false,*/
				sameAddress:false,

				displayField:1,

				relayStreet:null,
				relayCity:null,
				relayZipcode:null,
				newRelayStreet:null,
				newRelayCity:null,
				newRelayZipcode:null,
				changeAddressReference:0,
				listRelay:{},

				zindex:1000,
				mapDisplay:false,
			    markers: [],
			    place: null,
			    center:{},
			    //iconRelay:'/assets/front/images/spotlight-poi-u3dm.png',
			    iconRelay: {
					path: "M0-48c-9.8 0-17.7 7.8-17.7 17.4 0 15.5 17.7 30.6 17.7 30.6s17.7-15.4 17.7-30.6c0-9.6-7.9-17.4-17.7-17.4z",
					fillColor: "#960048",
					fillOpacity: 1,
					scale: .8,
					strokeColor: "white",
					strokeWeight: 1,
					labelOrigin: {x: 0, y: -25}
				},
				iconRelayOver: {
					path: "M0-48c-9.8 0-17.7 7.8-17.7 17.4 0 15.5 17.7 30.6 17.7 30.6s17.7-15.4 17.7-30.6c0-9.6-7.9-17.4-17.7-17.4z",
					fillColor: "#e60a7f",
					fillOpacity: 1,
					scale: .8,
					strokeColor: "white",
					strokeWeight: 1,
					labelOrigin: {x: 0, y: -25},
				}

			}
		},
		mounted (){
			//this.geolocate()
			if (this.type == 'print') {this.gtagGroupEvent = 'impression_form'}
			if (this.type == 'design') {this.gtagGroupEvent = 'project_form'}
			if (this.type == 'model') {this.gtagGroupEvent = 'model_form'}

		},
		computed: {
			...mapGetters([
				'print3dFiles',
				'makerSelected',
				'user3dm',
				'shippingSelected',
				'stepFormProcess',
				'stepFormProject',
				'addressShipping',
				'addressBilling',
				'user_pickup_address',
			]),
		},
		methods: {
			hoverMarker(markerIndex){
				this.$refs.markers[markerIndex].$markerObject.setIcon(this.iconRelayOver)
				this.$refs.markers[markerIndex].$markerObject.setZIndex(this.zindex++)
			},
			hoverMarkerOut(markerIndex){
				this.$refs.markers[markerIndex].$markerObject.setIcon(this.iconRelay)
				
			},
			selectRelay(obj){
				
				console.log ("Select Relay", this.type)
				// Google Tag Manager : push event account creation success
				//******************************************** */
				gtag_report_event(this.user3dm,this.gtagGroupEvent,this.gtagGroupEvent +'.delivery.confirm')
				//******************************************** */

				let addressShipping = {}

				addressShipping = {

					'firstname' : this.user3dm.address_billing.firstname,
					'lastname' : this.user3dm.address_billing.lastname,
					'company' : obj.name,
					'phone' : '',
					'street1' : obj.street1,
					'street2' : obj.street2,
					'zipcode' : obj.zipcode,
					'city' : obj.city,
					'country' : 'FR',
					'identifier': obj.id,
				}

				store.commit('SAVE_ADDRESS_SHIPPING',addressShipping)

				let addressBilling = {}

				addressBilling = {
					'firstname' : this.user3dm.address_billing.firstname,
					'lastname' : this.user3dm.address_billing.lastname,
					'company' : this.user3dm.address_billing.company,
					'phone' : this.user3dm.address_billing.phone,
					'street1' : this.user3dm.address_billing.street1,
					'street2' : this.user3dm.address_billing.street2,
					'zipcode' : this.user3dm.address_billing.zipcode,
					'city' : this.user3dm.address_billing.city,
					'country' : this.user3dm.address_billing.country
				}

				store.commit('SAVE_ADDRESS_BILLING',addressBilling)

				if(this.type == 'design'){
					store.commit('CHANGE_STEP_PROJECT',6)
				} else {
					store.commit('CHANGE_STEP',6)
				}

	  			setTimeout(function() {$('html,body').animate({scrollTop: $('#summary').offset().top},'slow');}, 500);


			},
			modifyRelayAddress(){

				this.relayStreet = this.newRelayStreet
				this.relayCity = this.newRelayCity
				this.relayZipcode = this.newRelayZipcode

				this.changeAddressReference = 0

				this.listingRelay()

			},
			listingRelay(){

				let data = {'street': this.relayStreet, 'zipcode': this.relayZipcode ,'city': this.relayCity }
				
				this.$http.post(this.apiChronopost, data ).then((response) => 
				{
					
					console.log('API Chronopost => success')
					var data = JSON.parse(response.body)

					this.listRelay = data

					if(this.listRelay.length == 0){
						this.mapDisplay = false
					} else {
						this.mapDisplay = true
					}
					



				}, (response) => {

					this.mapDisplay = false
					this.listRelay = {}
					console.log('API Chronopost => error')

				})


			},
			initShippingAddress(){
				this.shippingFirstname = null
				this.shippingLastname = null
				this.shippingCompany = null
				this.shippingPhone = null
				this.shippingAddress1 = null
				this.shippingAddress2 = null
				this.shippingZipcode = null
				this.shippingCity = null
				this.shippingCountry = 'FR'
			},
			actionAddress(){

				if( this.shippingSelected.key == 'pickup'){

					this.shippingFirstname = this.makerSelected.pickup.address.firstname
					this.shippingLastname = this.makerSelected.pickup.address.lastname
					if(!this.makerSelected.pickup.address.company){
						this.shippingCompany = this.makerSelected.pickup.address.firstname + ' ' + this.makerSelected.pickup.address.lastname
					} else {
						this.shippingCompany = this.makerSelected.pickup.address.company
					}
					
					this.shippingPhone = this.makerSelected.pickup.address.telephone
					this.shippingAddress1 = this.makerSelected.pickup.address.street1
					this.shippingAddress2 = this.makerSelected.pickup.address.street2
					this.shippingZipcode = this.makerSelected.pickup.address.zipcode
					this.shippingCity = this.makerSelected.pickup.address.city
					this.shippingCountry = this.makerSelected.pickup.address.country


				} else if( this.shippingSelected.key == 'relay' ){

					this.initShippingAddress()

					if( typeof this.user3dm.address_shipping != "undefined" && Object.keys(this.user3dm.address_shipping).length > 0 ){

						this.relayStreet = this.user3dm.address_shipping.street1 + ' ' 
						if(this.user3dm.address_shipping.street2){
							this.relayStreet = this.relayStreet + this.user3dm.address_shipping.street2
						}
						this.relayCity = this.user3dm.address_shipping.city
						this.relayZipcode = this.user3dm.address_shipping.zipcode
					
					}

					this.mapDisplay = true

				} else {

					this.initShippingAddress()


					if( typeof this.user3dm.address_shipping != "undefined" && Object.keys(this.user3dm.address_shipping).length > 0 ){

						this.shippingFirstname = this.user3dm.address_shipping.firstname
						this.shippingLastname = this.user3dm.address_shipping.lastname
						this.shippingCompany = this.user3dm.address_shipping.company
						this.shippingPhone = this.user3dm.address_shipping.phone
						this.shippingAddress1 = this.user3dm.address_shipping.street1
						this.shippingAddress2 = this.user3dm.address_shipping.street2
						this.shippingZipcode = this.user3dm.address_shipping.zipcode
						this.shippingCity = this.user3dm.address_shipping.city
						this.shippingCountry = this.user3dm.address_shipping.country
					
					}

				}

			},
			sameAsBilling(){

				if(Object.keys(this.user3dm.address_billing).length > 0){
					this.shippingFirstname = this.user3dm.address_billing.firstname
					this.shippingLastname = this.user3dm.address_billing.lastname
					this.shippingCompany = this.user3dm.address_billing.company
					this.shippingPhone = this.user3dm.address_billing.phone
					this.shippingAddress1 = this.user3dm.address_billing.street1
					this.shippingAddress2 = this.user3dm.address_billing.street2
					this.shippingZipcode = this.user3dm.address_billing.zipcode
					this.shippingCity = this.user3dm.address_billing.city
					this.shippingCountry = this.user3dm.address_billing.country
				}

			},
			validateAddress(){
				// Google Tag Manager : push event account creation success
				//******************************************** */
				gtag_report_event(this.user3dm,this.gtagGroupEvent,this.gtagGroupEvent +'.delivery.confirm')
				//******************************************** */



			    let validate = true

			    if(!this.shippingFirstname){
			    	$("#shipping-firstname").addClass("required-field")
			    	validate = false
			    } else {
			    	$("#shipping-firstname").removeClass("required-field")
			    }

			    if(!this.shippingLastname){
			    	$("#shipping-lastname").addClass("required-field")
			    	validate = false
			    } else {
			    	$("#shipping-lastname").removeClass("required-field")
			    }

			    if(!this.shippingPhone){
			    	$("#shipping-phone").addClass("required-field")
			    	validate = false
			    } else {
			    	$("#shipping-phone").removeClass("required-field")
			    }

			    if(!this.shippingZipcode){
			    	$("#shipping-zipcode").addClass("required-field")
			    	validate = false
			    } else {
			    	$("#shipping-zipcode").removeClass("required-field")
			    }

			    if(!this.shippingCity){
			    	$("#shipping-city").addClass("required-field")
			    	validate = false
			    } else {
			    	$("#shipping-city").removeClass("required-field")
			    }

			    if(!this.shippingAddress1){
			    	$("#shipping-address1").addClass("required-field")
			    	validate = false
			    } else {
			    	$("#shipping-address1").removeClass("required-field")
			    }


			    if(!validate){
			    	return
			    }



				let addressShipping = {}

				addressShipping = {

					'firstname' : this.shippingFirstname,
					'lastname' : this.shippingLastname,
					'company' : this.shippingCompany,
					'phone' : this.shippingPhone,
					'street1' : this.shippingAddress1,
					'street2' : this.shippingAddress2,
					'zipcode' : this.shippingZipcode,
					'city' : this.shippingCity,
					'country' : this.shippingCountry
				}

				store.commit('SAVE_ADDRESS_SHIPPING',addressShipping)

				let addressBilling = {}

				if(!this.user3dm.address_billing){

					addressBilling = addressShipping

				} else {

					addressBilling = {
						'firstname' : this.user3dm.address_billing.firstname,
						'lastname' : this.user3dm.address_billing.lastname,
						'company' : this.user3dm.address_billing.company,
						'phone' : this.user3dm.address_billing.phone,
						'street1' : this.user3dm.address_billing.street1,
						'street2' : this.user3dm.address_billing.street2,
						'zipcode' : this.user3dm.address_billing.zipcode,
						'city' : this.user3dm.address_billing.city,
						'country' : this.user3dm.address_billing.country
					}


				}

				this.displayField = 0
				store.commit('SAVE_ADDRESS_BILLING',addressBilling)

				if(this.type == 'design'){
					store.commit('CHANGE_STEP_PROJECT',6)
				} else {
					store.commit('CHANGE_STEP',6)
				}
	  			setTimeout(function() {$('html,body').animate({scrollTop: $('#summary').offset().top},'slow');}, 500);
			},
			updateAddress(){

				this.displayField = 1
				if(this.type == 'design'){
					store.commit('CHANGE_STEP_PROJECT',5)
				} else {
					store.commit('CHANGE_STEP',5)
				}
				setTimeout(function() {$('html,body').animate({scrollTop: $('#address').offset().top},'slow');}, 500);

			},
			saveAddress(){

			},
			/**
            * When the location found
            * @param {Object} addressData Data of the found location
            * @param {Object} placeResultData PlaceResult object
            * @param {String} id Input container ID
            */
            getAddressData: function (addressData, placeResultData, id) {
                this.address = addressData;
            },
		},
		watch: {
			listRelay(markers) {
		      if (markers.length > 2) {
		        const bounds = new google.maps.LatLngBounds()
		        for (let m of markers) {
		        	let latLng = {
		            	lat: m.coordinates.lat,
		            	lng: m.coordinates.lng,
					}
		          bounds.extend(latLng)
		        }
		        this.$refs.map.fitBounds(bounds)
		      }
		    },
			stepFormProcess: function () {

				if(this.stepFormProcess == 5){

					this.actionAddress()
					this.listingRelay()

				}

		    },
		    stepFormProject: function () {

				if(this.stepFormProject == 5){

					this.actionAddress()
					this.listingRelay()

				}

		    },
		    shippingSelected: function() {

		    	if(this.stepFormProcess == 5 || this.stepFormProject == 5){

					this.actionAddress()
					this.sameAddress = false

				}

		    },
		    sameAddress: function(){

		    	if(this.sameAddress){
		    		this.sameAsBilling()
		    	}
		    },

		}
	}
</script>

<style>

</style>