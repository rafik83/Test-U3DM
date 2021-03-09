<template>
	<div>
		<div id="makers" class="col-sm-12 pad-15-0 mrg-t-20">
			<h2 class=" bg-white pad-15 " v-if="type == 'design'"><span class="rounded">2</span>{{label1_1}}</h2>
            <h2 class=" bg-white pad-15 " v-else><span class="rounded">2</span> Choisissez le maker qui correspond à votre projet</h2>
			
			<!-- <div class="col-sm-12 bg-white pad-15 mrg-10-0" v-show="makersListComputed.length > 1 || pickupRequired"> -->
			<div class="col-sm-12 bg-white pad-15 mrg-10-0" v-show="makersListComputed.length > 1 || type != 'design' ">
				<div class="col-sm-6 pad-10">
					<p class="p-filter">Trier par : </p>
					<button id="btn-filter-price" class="btn-filter btn-filter-on" v-on:click="filtered('price')" v-show="makersListComputed.length > 1">Prix</button>
					<button id="btn-filter-popularity" class="btn-filter" v-on:click="filtered('popularity')" v-show="makersListComputed.length > 1">Popularité</button>
					<button id="btn-filter-rating" class="btn-filter" v-on:click="filtered('rating')" v-show="makersListComputed.length > 1">Notation</button>
					<button id="btn-filter-distance" class="btn-filter" v-on:click="filtered('distance')" v-show="pickupRequired && makersListComputed.length > 1" >Distance</button>
					<button id="btn-filter-alpha" class="btn-filter" v-on:click="filtered('alpha')" v-show="makersListComputed.length > 1">A-Z</button>
				</div>
				<div class="col-sm-3 pad-10" style="float:right">
					<input type="text" v-model="filterSearch" class="form-control" placeholder="Rechercher ...">
				</div>
				<div class="col-sm-3 pad-10" v-show="pickupPossible && type != 'design'" style="float:right">
					<label class="label-pickup-required">
					    <input type="checkbox" name="pickup-required" v-model="pickupRequired" value="true">
					    <span></span>
					    <span class="wrapped-label pad-10">Retrait sur place</span>
					</label>
				</div>
				<div class="col-sm-12 pad-10" v-show="pickupRequired">
					<gmap-autocomplete :value="description"
				        @place_changed="setPlace"
				        placeholder="Saisissez votre addresse ..."
				        :select-first-on-enter="true"
				        class="col-sm-10">
				    </gmap-autocomplete>
				    <div class="col-sm-2 label-pickup-required">
				    	<span zb-tooltip="Définir ma position" zb-tooltip-position="bottom">
				    		<i class="fas fa-crosshairs fa-2x"  v-on:click="geolocation()"></i>
				    	</span>
				    </div>
				    
				</div>
			</div>
			<!-- <div class="row" v-for="maker in makersListComputed"> -->
			<div v-for="maker in makersListComputed" :class="{'border-maker':(maker.id == makerSelected.id)}" class="col-sm-12 maker-list  bg-white pad-15 mrg-10-0" :key="maker.id" :id="'maker-'+maker.id">
				<div class="col-sm-2 text-center">
					<div class="rounded-pics">
						<img :src="maker.logo" :alt="maker.name">
					</div>
					<h3>{{maker.name}}</h3>
				</div>
				<div class="col-sm-6">
					<p class="text-justify txt-default h5" v-html="maker.bio"></p>
					<br>
					<div class="col-sm-12" v-if="type == 'design'">
						<div class="btn btn-sm mrg-0" data-toggle="modal" data-target="#quotation-modal" v-on:click="viewQuotation(maker)">
							Voir le devis détaillé
						</div>
					</div>
					<div class="col-sm-4 text-center pad-5">
						<i class="fas fa-map-marker-alt fa-2x" v-show="maker.pickup.available"></i>
						<div class="thin" v-if="maker.pickup.available">
							{{maker.pickup_address.city}}, {{maker.pickup_address.country}}
						</div>
						<div class="thin" v-if="maker.pickup.available">
							Retrait sur place possible <br>
							<span v-show="maker.pickup.distance > 0 && pickupRequired" class="txt-purple">
								+- {{ maker.pickup.distance/1000 }} Km
							</span>
						</div>
					</div>
					<div class="col-sm-4 text-center pad-5">
						<div class="col-sm-12 pad-0" v-show="maker.rating">
	                          <i class="fas fa-star fa-1x" v-show="maker.rating >= 1" title="A"></i>
	                          <i class="fas fa-star fa-1x" v-show="maker.rating >= 2" title="B"></i>
	                          <i class="fas fa-star fa-1x" v-show="maker.rating >= 3" title="C"></i>
	                          <i class="fas fa-star fa-1x" v-show="maker.rating >= 4" title="D"></i>
	                          <i class="fas fa-star fa-1x" v-show="maker.rating == 5" title="E"></i>
	                          <i class="fas fa-star-half-alt" v-show="maker.rating == 0.5"></i>
	                          <i class="fas fa-star-half-alt" v-show="maker.rating == 1.5"></i>
	                          <i class="fas fa-star-half-alt" v-show="maker.rating == 2.5"></i>
	                          <i class="fas fa-star-half-alt" v-show="maker.rating == 3.5"></i>
	                          <i class="fas fa-star-half-alt" v-show="maker.rating == 4.5"></i>
							  <i class="far fa-star fa-1x" v-show="maker.rating < 1.5 "></i>
							  <i class="far fa-star fa-1x" v-show="maker.rating < 2.5 "></i>
							  <i class="far fa-star fa-1x" v-show="maker.rating < 3.5 "></i>
							  <i class="far fa-star fa-1x" v-show="maker.rating < 4.5 "></i> 
	                    </div>
	                    <div class="col-sm-12 pad-0">
	                    	<a class="txt-purple" v-show="maker.rating" v-on:click="openRating(maker.name,maker.comments)" data-toggle="modal" data-target="#comment-modal">Voir tous les avis</a>
	                    </div>
					</div>
					<div class="col-sm-4 text-center pad-5">
						<span  v-show="maker.productions"> 
							<strong>{{ maker.productions }}</strong>
							{{maker.productions | pluralize('projet')}} {{maker.productions | pluralize('réalisé')}}
						</span>
					</div>
				</div>
				
				<div class="col-sm-4 col-sm-4 text-right">
					<!-- {{maker.pictures}} -->

					<div :id="'carousel-maker-'+maker.id" class="carousel slide" data-ride="carousel" v-show="maker.portfolio != null">
					  <!-- Indicators -->
					  <ol class="carousel-indicators">
					    <li v-for="(photoNumber, index) in maker.portfolio" :data-target="'#carousel-maker-'+maker.id" :data-slide-to="index" v-bind:class="{ active: index == 0 }" ></li>
					  </ol>

					  <!-- Wrapper for slides -->
					  <div class="carousel-inner" role="listbox">
					    <div v-for="(photo, index) in maker.portfolio" v-bind:class="{ active: index == 0 , item : photo }" >
					      <img :src="photo" data-toggle="modal" :data-target="'#imagemodal-'+maker.id">
					    </div>

					  </div>

					  <!-- Controls -->
					  <a class="left carousel-control" :href="'#carousel-maker-'+maker.id" role="button" data-slide="prev">
					    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
					    <span class="sr-only">Previous</span>
					  </a>
					  <a class="right carousel-control" :href="'#carousel-maker-'+maker.id" role="button" data-slide="next">
					    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
					    <span class="sr-only">Next</span>
					  </a>
					</div>
						<!-- Modal BIG PORTFOLIO -->
					  	<div class="modal fade" :id="'imagemodal-'+maker.id" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
						  <div class="modal-dialog">
						    <div class="modal-content">
						      <div class="modal-body">
						        <div :id="'carousel-maker-big-'+maker.id" class="carousel slide" data-ride="carousel" v-show="maker.portfolio != null">
									  <!-- Indicators -->
									  <ol class="carousel-indicators">
									    <li v-for="(photoNumber, index) in maker.portfolio" :data-target="'#carousel-maker-big-'+maker.id" :data-slide-to="index" v-bind:class="{ active: index == 0 }" ></li>
									  </ol>

									  <!-- Wrapper for slides -->
									  <div class="carousel-inner" role="listbox">
									    <div v-for="(photo, index) in maker.portfolio" v-bind:class="{ active: index == 0 , item : photo }" >
									      <img :src="photo" data-toggle="modal" data-target="#imagemodal">
									    </div>

									  </div>

									  <!-- Controls -->
									  <a class="left carousel-control" :href="'#carousel-maker-big-'+maker.id" role="button" data-slide="prev">
									    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
									    <span class="sr-only">Previous</span>
									  </a>
									  <a class="right carousel-control" :href="'#carousel-maker-big-'+maker.id" role="button" data-slide="next">
									    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
									    <span class="sr-only">Next</span>
									  </a>
									</div>
						      </div>
						    </div>
						  </div>
						</div>
						<!-- END MODAL -->
					<!-- </div> -->
					
					<div class="text-right txt-purple h3">
						{{maker.display_price_excl }} HT
					</div>
					<div class="text-right">
						<p>{{maker.display_price_incl }} TTC</p>
					</div>
					<div class="btn btn-sm mrg-0" v-on:click="selectMaker(maker.id,maker.name,maker.price_incl,maker.price_excl,maker.total_options_incl,maker.total_options_excl,maker.pickup,maker.finishing,maker.pickup.distance,maker.quotation)" v-show="expiredDateValidity == false">
						Sélectionner ce maker
					</div>
					<div v-show="expiredDateValidity == true">
						Période de validité des devis dépassée. <br>
						Vous pouvez contacter United-3d-Makers.
					</div>
				</div>
			</div>
    </div>
    <!-- Modal  Rating -->
	<div class="modal fade" id="comment-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content" style="background-color:#FFFFFF;">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title" id="myModalLabel">{{ commentsName }}</h4>
	      </div>
	      <div class="modal-body">
	        <div v-for="comment in comments" style="color:#000">
	        	<p style="margin-bottom:0px;">{{ comment.comment}}</p>
	        	<p>
                    <i class="fas fa-star fa-1x" v-show="comment.rate >= 1"></i>
                    <i class="fas fa-star fa-1x" v-show="comment.rate >= 2"></i>
                    <i class="fas fa-star fa-1x" v-show="comment.rate >= 3"></i>
                    <i class="fas fa-star fa-1x" v-show="comment.rate >= 4"></i>
                    <i class="fas fa-star fa-1x" v-show="comment.rate == 5"></i>
                    <i class="fas fa-star-half-alt" v-show="comment.rate == 0.5"></i>
                    <i class="fas fa-star-half-alt" v-show="comment.rate == 1.5"></i>
                    <i class="fas fa-star-half-alt" v-show="comment.rate == 2.5"></i>
                    <i class="fas fa-star-half-alt" v-show="comment.rate == 3.5"></i>
                    <i class="fas fa-star-half-alt" v-show="comment.rate == 4.5"></i>
			  		<i class="far fa-star fa-1x" v-show="comment.rate < 1.5 "></i>
			  		<i class="far fa-star fa-1x" v-show="comment.rate < 2.5 "></i>
			  		<i class="far fa-star fa-1x" v-show="comment.rate < 3.5 "></i>
			  		<i class="far fa-star fa-1x" v-show="comment.rate < 4.5 "></i> 
			  		- {{ comment.date }}
	       		</p>
	        </div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
	      </div>
	    </div>
	  </div>
	</div>
	<!-- End Modal Rating -->
    <!-- Modal  Quotation -->
	<div class="modal fade" id="quotation-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content" style="background-color:#FFFFFF;">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title" id="myModalLabel">Devis N°{{ quotation.reference }} | Délai de réalisation {{ quotation.production_time }} jour(s)</h4>
	      </div>
	      <div class="modal-body">
	        <div style="color:#000; display:block; overflow:none;">
	        	<p style="margin-bottom:0px;"><textarea rows="7" readonly disabled >{{ quotation.description }}</textarea></p>
				<br>
	        	<table class="col-sm-12 table table-striped table-hover">
	        		<thead>
						<tr>
						    <th >Libellé</th>
						    <th class="text-center">Qté</th>
						    <th class="text-right">PU</th>
						    <th class="text-right">Total</th>
						</tr>
					</thead>
			  		<tbody>
			  			<tr v-for="line in quotation.lines">
			  				<td>{{ line.description }}</td>
			  				<td class="text-center">{{ line.quantity }}</td>
			  				<td class="text-right">{{ line.price | pricing }}</td>
			  				<td class="text-right">{{ (line.price*line.quantity) | pricing }}</td>
			  			</tr>
			  			<tr>
			  				<td colspan="3" class="text-right">TOTAL HT</td>
			  				<td class="text-right">{{ quotation.total_ht | pricing }}</td>
			  			</tr>
			  			<tr>
			  				<td colspan="3" class="text-right">TVA</td>
			  				<td class="text-right">{{ quotation.tva | pricing }}</td>
			  			</tr>
			  			<tr>
			  				<td colspan="3" class="text-right">TOTAL TTC</td>
			  				<td class="text-right">{{ quotation.total | pricing }}</td>
			  			</tr>
			  		</tbody>
	        	</table>
	        	&nbsp;
	        	<div class="col-sm-12 text-center">
	        	    <p>Devis valide jusqu'au : {{ quotation.quotation_validity}}</p>
                </div><br>
	        	<div class="col-sm-12 text-center">
	        		<a :href="quotation.download_pdf" class="btn btn-default" target="_blank">Télécharger le devis</a>
	        	</div><br>
	        	<div class="col-sm-12 text-center">
	        	    <a :href="quotation.link_quotation_see">Communiquer avec le maker sur ce devis</a>
                </div><br>
	        	&nbsp;
	        </div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
	      </div>
	    </div>
	  </div>
	</div>
	<!-- End Modal Quotation -->
    </div>
</template>


<script>
	
	import Vue from 'vue'
	import { mapGetters } from 'vuex'
	import store from '../stores/PrintStore'


	export default {
		name: "makerFile",
		store: store,
		props: [
			'apiFees',
			'productId',
			'type',
			'tagSpecial',
		],
		data: function(){
			return {
				filter : 'price',
				filterSearch: '',
				pickupRequired : false,
				pickupPossible : false,

				description: '',
      			latLng: {},
      			currentLocation: {},
      			initPlace : false,
      			comments : {},
      			commentsName : '',
				quotation:{},
				label1_1 : '',
			}
		},
		mounted (){

			var makerview = false
			for (const key in this.makersList){makerview = true}
			if (makerview) {gtag_report_event(this.user3dm,'project_form','project_form.makers.view')}
			
			if(this.type == 'design'){
				$('.maker-list').removeClass('border-maker');
				this.label1_1 = 'Des devis';
				if (tagSpecial == "COVID") {
					this.label1_1 = "Votre devis";
					}

			}
		},
		computed: {
			...mapGetters([
				'print3dFiles',
				'makersList',
				'stepFormProcess',
				'user3dm',
				'makerSelected',
				'projectOrigin',
				'expiredDateValidity',
			]),
			makersListComputed: function(){

				this.pickupPossible = false

				//$('.maker-list').removeClass('border-maker');

				let makersTab = []

				for (const key in this.makersList){
					//Addition price for this maker for each product
					let priceMakerIncl = 0
					let priceMakerExcl = 0
					let totalOptionsIncl = 0
					let totalOptionsExcl = 0
					let listFinitionByMaker = [];

					//console.log('Price',this.makersList[key].price_tax_incl)
					//console.log('type',this.type)

					if(this.type == 'design'){

						priceMakerIncl = this.makersList[key].price_tax_incl;
						priceMakerExcl = this.makersList[key].price_tax_excl;
						totalOptionsIncl = 0;
						totalOptionsExcl = 0;
						listFinitionByMaker = [];

					} else {

						for(const product in this.print3dFiles){
							for(const price in this.print3dFiles[product].makersList){
								if(this.print3dFiles[product].makersList[price].id == this.makersList[key].id){
									priceMakerIncl += this.print3dFiles[product].makersList[price].price_tax_incl
									priceMakerExcl += this.print3dFiles[product].makersList[price].price_tax_excl
								}
							}
						}
						
						for(const productOption in this.print3dFiles){
							for(const price in this.print3dFiles[productOption].makersList){

								if(this.print3dFiles[productOption].makersList[price].id == this.makersList[key].id){
									/*priceMakerIncl += this.print3dFiles[product].makersList[price].price_tax_incl
									priceMakerExcl += this.print3dFiles[product].makersList[price].price_tax_excl*/
									for(const option in this.print3dFiles[productOption].makersList[price].finishing){

										totalOptionsIncl += this.print3dFiles[productOption].makersList[price].finishing[option].pricettc
										totalOptionsExcl += this.print3dFiles[productOption].makersList[price].finishing[option].priceht

										listFinitionByMaker.push(this.print3dFiles[productOption].makersList[price].finishing[option])

									}
								}
							}
						}

					}


					let logoPath = '/assets/front/images/maker-avatar.png';
					if (null != this.makersList[key].pictures.profile) {
					    logoPath = this.makersList[key].pictures.profile;
					}

					makersTab.push({
						'name' : this.makersList[key].name,
						'id' : this.makersList[key].id,
						'display_price_incl' : new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format((priceMakerIncl+totalOptionsIncl)/100),
						'display_price_excl' : new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format((priceMakerExcl+totalOptionsExcl)/100),
						'price_incl' : priceMakerIncl,
						'price_excl' : priceMakerExcl,
						'bio' : this.makersList[key].bio,
						'rating' : this.makersList[key].rating,
						'quotation' : this.makersList[key].quotation,
						'comments' : this.makersList[key].comments,
						'productions' : this.makersList[key].productions,
						'logo' : logoPath,
						'portfolio' : this.makersList[key].pictures.portfolio,
						'pickup' : this.makersList[key].pickup,
						/*'pickup_distance' : this.makersList[key].pickup.distance,*/
						'pickup_address' : this.makersList[key].pickup_address,
						'total_options_incl' : totalOptionsIncl,
						'total_options_excl' : totalOptionsExcl,
						'display_total_options_incl' : new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format((totalOptionsIncl)/100),
						'display_total_options_excl' : new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format((totalOptionsExcl)/100),
						'finishing': listFinitionByMaker,
					})

					// pickupPossible
					if(this.makersList[key].pickup.available == true){
						this.pickupPossible = true
					}
				}

				// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/sort
				if(this.filter == 'price'){

					makersTab.sort(function (a, b) {
					  return   (a.total_options_excl+a.price_excl) - (b.total_options_excl+b.price_excl)
					})

				} else if(this.filter == 'alpha') {

					// https://stackoverflow.com/questions/6712034/sort-array-by-firstname-alphabetically-in-javascript
					makersTab.sort(function (a, b) {
						if(a.name < b.name) return -1
					    if(a.name > b.name) return 1
					    return 0
					})

				} else if(this.filter == 'popularity') {

					makersTab.sort(function (a, b) {
					  return  b.productions - a.productions
					})

				} else if(this.filter == 'rating') {

					makersTab.sort(function (a, b) {
					  return  b.rating - a.rating
					})

				} else if(this.filter == 'distance') {

					makersTab.sort(function (a, b) {
					  return  a.pickup.distance - b.pickup.distance
					})

				} else {

					makersTab.sort(function (a, b) {
					  return  (b.total_options_excl-b.price_excl) - (a.total_options_excl-a.price_excl)
					})

				}

				if(this.filterSearch != ''){

					var self = this

					makersTab = makersTab.filter(maker => {

				        let string  = maker.name + ' ' + maker.bio

				        return string.toLowerCase().includes(self.filterSearch.toLowerCase())
				        
				    })

				}

				if(this.pickupRequired == true){

					var self = this

					makersTab = makersTab.filter(maker => {

				        return maker.pickup.available === true
				    })

				}

				return makersTab

			}
		},
		methods: {
			openRating : function(name,comments){

				console.log('PopupRating',comments)

				this.commentsName = name
				this.comments = comments



			},
			calculateDistance : function(){
			// https://developers.google.com/maps/documentation/javascript/distancematrix

				let service = new google.maps.DistanceMatrixService()
				let origin = new google.maps.LatLng(this.currentLocation.lat, this.currentLocation.lng)

				for (const index in this.makersList){

					if(this.makersList[index].pickup.available){

						let address = this.makersList[index].pickup.address

						let destination = address.street1 + ' ' + address.street2 + ', ' + address.zipcode + ' ' + address.city + ', ' + address.country

						service.getDistanceMatrix(
						  {
						    origins: [origin],
						    destinations: [destination],
						    travelMode: 'DRIVING',
						    unitSystem: google.maps.UnitSystem.METRIC,
						  }, callback)

						self = this

						function callback(response, status) {
							
							console.log('Distance => ',response)
							store.commit('ADD_DISTANCE_MAKER',{ 'makerIndex' : index, 'distance' : response.rows[0].elements[0].distance.value })

							// Force re-computed variable
							self.$forceUpdate()
						}
					}
				}
			},
			geolocation : function() {
		      navigator.geolocation.getCurrentPosition((position) => {

		        this.currentLocation = {
		          lat: position.coords.latitude,
		          lng: position.coords.longitude
		        }

		        this.calculateDistance()

		        this.setDescription('Ma position : ' + this.currentLocation.lat + ' | ' + this.currentLocation.lng)

		      })
		    },
		    geocodeAddress : function(address){

		    	let geocoder = new google.maps.Geocoder()

		    	self = this

		    	geocoder.geocode( { 'address': address}, function(results, status) {
			    	if (status == 'OK') {

			        	console.log('Geocoder address succes')
			        	self.currentLocation = {
		          			lat: results[0].geometry.location.lat(),
		          			lng: results[0].geometry.location.lng()
		        		}

		        		self.calculateDistance()

			    	} else {

			        	alert('Geocode was not successful for the following reason: ' + status)
			        	this.description = ''

			      	}
			    })
		    },
			setDescription(description) {
		        this.description = description;
		        store.commit('UPDATE_USER_PICKUP_ADDRESS', this.description)
		        //Storage addresse for address component
		        //store.commit('UPDATE_USER_PICKUP_ADDRESS',description)
		    },
		    setPlace(place) {

		    	if (place){

		    		//console.log('PLACE',place)
		    		this.initPlace = true

		    		store.commit('UPDATE_USER_PICKUP_ADDRESS', place.formatted_address)

		    		this.currentLocation = {
			          lat: place.geometry.location.lat(),
			          lng: place.geometry.location.lng(),
			        }

			        this.calculateDistance()

		    	}
		    },
			filtered(filter){

				console.log('Method filtered :',filter)
				this.filter = filter

				$('.btn-filter-on').removeClass('btn-filter-on')

				$('#btn-filter-'+filter).addClass('btn-filter-on')


			},
			selectMaker(idMaker,nameMaker,price_incl,price_excl,total_options_incl = 0,total_options_excl,pickup,finishing,distance,quotation = null){

				//$('.maker-list').removeClass('border-maker')

				//$('#maker-'+idMaker).addClass('border-maker')
				//Save price in product Array with selected maker
				this.savePrice(idMaker)

				//console.log('PICKUUUUUUPPPP !', pickup)
				var tagSpec = null
				if ((this.type == 'design') && (projectOrigin != null)){
					this.originalProject = JSON.parse(projectOrigin)
					tagSpec=this.originalProject.type[0].tagSpec
				} 

				//Calculate Fees
				let data = {'amount': price_excl + total_options_excl , 'taggSpec' : tagSpec}
				
				this.$http.post(this.apiFees, data ).then((response) => 
				{
					console.log('API FEES => success')
					var data = JSON.parse(response.body)

					store.commit('SET_FEES', {'fees_excl' : data.fee_tax_excl , 'fees_incl' : data.fee_tax_incl })
					store.commit('REMOVE_SHIPPING', null)



				}, (response) => {

					console.log('API Shipping => error',response)

				})

				store.commit('MAKER_SELECT',{'id':idMaker , 'name':nameMaker, 'price_excl':price_excl,'price_incl':price_incl, 'pickup':pickup, 'finishing':finishing,'total_options_incl': total_options_incl,'total_options_excl': total_options_excl,'distance': distance,'quotation':quotation})


				if(this.type == 'design'){

					if(this.projectOrigin.shipping_required == true){

						store.commit('CHANGE_STEP_PROJECT',3)

						setTimeout(function() {$('html,body').animate({scrollTop: $('#shipping').offset().top},'slow');}, 200);

					} else {

						store.commit('CHANGE_STEP_PROJECT',6);

						setTimeout(function() {$('html,body').animate({scrollTop: $('#summary').offset().top},'slow')}, 200);



					}
					// Google Tag Manager : push event Maker selected (Design)
					//******************************************** */
					gtag_report_event(this.user3dm,'project_form','project_form.makers.selected')		
					//******************************************** */

				} else {

					store.commit('CHANGE_STEP',3)
					// Google Tag Manager : push event Maker selected (Print)
					//******************************************** */
					gtag_report_event(this.user3dm,'impression_form','impression_form.makers.selected')		
					//******************************************** */
					setTimeout(function() {$('html,body').animate({scrollTop: $('#shipping').offset().top},'slow');}, 200);
				}
				

				//console.log('Maker selected =>', idMaker)
			},
			savePrice(idMaker){

				for(const product in this.print3dFiles){
					for(const price in this.print3dFiles[product].makersList){
						if(this.print3dFiles[product].makersList[price].id == idMaker){
							
							store.commit('UPDATE_3DFILE_PRICES', {
								'productId' : this.print3dFiles[product].fileNumber,
								'priceTaxEcl' : this.print3dFiles[product].makersList[price].price_tax_excl,
								'priceTaxEclDisplay' : new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(this.print3dFiles[product].makersList[price].price_tax_excl/100),
								'priceTaxInc' : this.print3dFiles[product].makersList[price].price_tax_incl,
								'priceTaxEclUnit' : (this.print3dFiles[product].makersList[price].price_tax_excl / this.print3dFiles[product].quantity) ,
								'priceTaxEclUnitDisplay' : new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format((this.print3dFiles[product].makersList[price].price_tax_excl / this.print3dFiles[product].quantity)/100) ,
								'priceTaxIncUnit' : (this.print3dFiles[product].makersList[price].price_tax_incl / this.print3dFiles[product].quantity) ,
								'finishing' : this.print3dFiles[product].makersList[price].finishing
							})

						}
					}
				}


			},
			viewQuotation(maker){
				this.quotation = maker.quotation;
				this.quotation.total = maker.price_incl;
				this.quotation.total_ht = maker.price_excl;
				this.quotation.tva = maker.price_incl - maker.price_excl;
			},
		},
		watch:{
			currentLocation: function(position){

				console.log('CurrentLocation change :',position)

			},
			pickupRequired: function(val){

				if(true === val && false === this.initPlace){

					if(Object.keys(this.user3dm).length === 0){

						return

					}

					if(Object.keys(this.user3dm.address_shipping).length > 0){

						let address = this.user3dm.address_shipping.street1 + ' '

						if(this.user3dm.address_shipping.street2){

							address += this.user3dm.address_shipping.street2 + ' '

						}

						address += ', ' + this.user3dm.address_shipping.zipcode + ' ' + this.user3dm.address_shipping.city +', ' + this.user3dm.address_shipping.country

						this.description = address

						this.geocodeAddress(address)

					}
					

				}

			},
			stepFormProcess : function(val){

				if(val < 2 ){

					this.pickupRequired = false

				}

			}
		},
		filters: {
		  pluralize: function (value, string) {

		  	if(value > 1){

		  		return string+'s'

		  	} else {

		  		return string

		  	}

		  },
		  pricing: function (value) {

		  	return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format((value)/100)
		    
		  },
		},
	}
</script>

<style>

</style>