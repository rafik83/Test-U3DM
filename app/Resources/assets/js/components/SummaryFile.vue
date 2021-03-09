<template>
	<div>
		<div id="summary" class="col-sm-12 bg-white pad-15 mrg-t-40">
            <h2>
            	<span class="rounded" v-if="type == 'design' && shippingRequired != true">3</span>
				<span class="rounded" v-else-if="type == 'basket'">2</span>
				<span class="rounded" v-else>5</span>

            	 Validez votre commande</h2>
    	
	    	<table class="table table-hover">
			  <tbody>
			  	<tr v-if="type != 'design' && type != 'basket'">
			  		<td colspan="4">
				  	<table class="table table-hover" v-for="product in productPrice">
				  		<thead>
						    <tr>
						    	<th scope="col">Nom du fichier</th>
						    	<th scope="col">Qté</th>
						    	<th scope="col">PU</th>
						    	<th scope="col" class="text-right">Total</th>
						    </tr>
						</thead>
			  			<tbody>
					  		<tr>
						    	<td><strong>{{ product.filename }}</strong></td>
						    	<td>{{ product.quantity }}</td>
						    	<td class="">{{ product.priceTaxEclUnitDisplay }}</td>
						    	<td class="text-right">{{ product.priceTaxEclDisplay }} HT</td>
					    	</tr>
					    	<tr v-for="option in product.finishing">
					    		<td>&nbsp;&nbsp;&nbsp;&nbsp;Finition : {{ option.title }}</td>
						    	<td>{{ product.quantity }}</td>
						    	<td class="">{{ (option.priceht/product.quantity) | pricing }}</td>
						    	<td class="text-right">{{ option.priceht | pricing }} HT</td>
					    	</tr>
				    	</tbody>
				  	</table>
				  	</td>
			  	</tr>
				<tr v-else-if="type == 'basket'">
			  		<td colspan="4">
				  	<table class="table table-hover">
				  		<thead>
						    <tr>
						    	<th scope="col">Nom du modèle</th>
						    	<th scope="col" class="text-right">Total</th>
						    </tr>
						</thead>
			  			<tbody>
					  		<tr v-for="item in currentBasket">
						    	<td><strong>{{ item.name }}</strong></td>
						    	<td class="text-right">{{ item.priceTaxExcl }} HT</td>
					    	</tr>
				    	</tbody>
				  	</table>
				  	</td>
			  	</tr>
			  	<tr v-else>
			  		<td colspan="4">
				  	<table class="table table-hover">
				  		<thead>
						    <tr>
						    	<th scope="col">Libellé</th>
						    	<th scope="col">Qté</th>
						    	<th scope="col">PU</th>
						    	<th scope="col" class="text-right">Total</th>
						    </tr>
						</thead>
			  			<tbody>
					    	<tr v-for="line in quotation.lines">
					    		<td>{{ line.description }}</td>
						    	<td>{{ line.quantity }}</td>
						    	<td class="">{{ (line.price) | pricing }}</td>
						    	<td class="text-right">{{ (line.price*line.quantity) | pricing }} HT</td>
					    	</tr>
				    	</tbody>
				  	</table>
				  	</td>
			  	</tr>
			    <tr v-show="fees.fees_excl > 0">
			    	<td colspan="2" class="col-sm-6"></td>
			    	<td class="text-right">Frais de service</td>
			    	<td class="text-right">{{ feesService }} HT</td>
			    </tr>
			    <tr v-show="coupon.name">
			    	<td colspan="2" class="col-sm-6"></td>
			    	<td class="text-right">{{ coupon.name }}</td>
			    	<td class="text-right">- {{ couponFormat }} HT</td>
			    </tr>
			    <tr>
			    	<td colspan="2" class="col-sm-6"></td>
			    	<td class="text-right">TVA</td>
			    	<td class="text-right">{{ orderVat }}</td>
			    </tr>
			    <tr v-if="type != 'basket'">
			    	<td colspan="2" class="col-sm-6"></td>
			    	<td class="text-right">Frais de port</td>
			    	<td class="text-right">{{ shipping }}</td>
			    </tr>
			    <tr>
			    	<td colspan="2" class="col-sm-6"></td>
			    	<td class="text-right"><strong>TOTAL</strong></td>
			    	<td class="text-right"><strong>{{ total }} TTC</strong></td>
			    </tr>
			  </tbody>
			</table>
			<div v-if="type != 'basket'" class="col-sm-12 text-right">
				<div class="col-sm-7 pad-0 text-right" v-if="couponApply" style="line-height:36px;">
					Coupon : <strong>{{ couponName }}</strong>
				</div>
				<div class="col-sm-7 pad-0 left" v-else>
					<input type="text" v-model="couponName" id="coupon-name" class="form-control" placeholder="Code coupon">
					<div class="col-sm-12 pad-0 text-left" v-show="couponError" style="padding-left:15px;line-height:1px;font-size:12px;">
						<strong>{{ couponError }}</strong>
					</div>
				</div>
				<div class="col-sm-5 pad-0 right">
					<button class="btn btn-default btn-rounded btn-grey" @click="applyCoupon" :disabled="processBtnApply" v-if="!couponApply">
						Appliquer le coupon
					</button>
					<button class="btn btn-default btn-rounded btn-grey" @click="deleteCoupon" v-else>
						Supprimer le coupon
					</button>
				</div>
			</div>
			<div class="col-sm-12 text-right" style="margin-top:20px;">
				<textarea v-if="type != 'basket'" v-model="instruction" rows="4" maxlength="500" placeholder="Complément d'information ..."></textarea>
	    		<button v-on:click="validateSummary" class="btn btn-default btn-rounded">
	            	VALIDER MA COMMANDE
	            </button>
	    	</div>
	    </div>
    </div>
</template>


<script>
	
	import Vue from 'vue'
	import { mapGetters } from 'vuex'
	import store from '../stores/PrintStore'


	export default {
		name: "summaryFile",
		store: store,
		props: [
			'apiCoupon',
			'type',
			'shippingRequired',
		],
		data: function(){
			return {
				instruction :'',
				couponName: '',
				couponApply: false,
				processBtnApply: false,
				couponError: '',
				totalNotFormatted: 0,
			}
		},
		mounted (){


		},
		computed: {
			...mapGetters([
				'print3dFiles',
				'makersList',
				'stepFormProcess',
				'makerSelected',
				'shippingSelected',
				'fees',
				'coupon',
				'discount_excl',
				'discount_incl',
				'user3dm',
				'currentBasket',
				'stripeIntentSecret',
				'stripeIntentId'
			]),
			productPrice : function(){
				 return this.print3dFiles
			},
			quotation: function(){
				return this.makerSelected.quotation
			},
			couponFormat: function(){

				if(this.discount_excl){

					return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format((this.discount_excl)/100)

				} else {

					return 0
				}

			},
			orderVat : function(){
				if(this.type === 'basket') {
					var priceHT = 0;
					var priceTTC = 0;
					var TVA = 0;
					for (var item in this.currentBasket) {
						//console.log(this.currentBasket);
						priceHT += this.currentBasket[item].priceTaxExcl;
						priceTTC += this.currentBasket[item].priceTaxIncl;
					}
					TVA = priceTTC - priceHT;
					return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(TVA)
				}
				else {
					if(this.makerSelected.price_excl > 0){
						
						let vat = 0

						vat = this.makerSelected.price_incl - this.makerSelected.price_excl

						if(this.makerSelected.total_options_excl > 0){
							vat += this.makerSelected.total_options_incl - this.makerSelected.total_options_excl
						}

						if(this.fees.fees_excl > 0){
							vat += this.fees.fees_incl - this.fees.fees_excl
						}

						if(this.discount_excl > 0){

							vat -= this.discount_incl - this.discount_excl 

						}

						return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format((vat)/100)

					} else {

						return 0
					}
				}

			},
			feesService : function(){

				if(this.fees.fees_excl > 0){

					return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(this.fees.fees_excl/100)

				} else {

					return 0
				}

			},
			shipping: function(){

				if(this.shippingSelected.key == 'pickup'){
					return 'Gratuit'
				} else {
					if(this.shippingSelected.price > 0){
						return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format((this.shippingSelected.price)/100)
					} else {
						return 0
					}
				
				}
			},
			total: function(){
				if(this.type === 'basket') {
					var priceTTC = 0;
					for (var item in this.currentBasket) {
						priceTTC += this.currentBasket[item].priceTaxIncl;
					}
					return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(priceTTC)
				}
				else {
					if(this.makerSelected.price_incl > 0){

						let total = 0
						total += this.makerSelected.price_incl
						if(this.fees.fees_incl > 0){
							total += this.fees.fees_incl
						}
						if(this.shippingSelected.key!= 'pickup' && !isNaN(this.shippingSelected.price)){
							total += this.shippingSelected.price
						}
						if(this.makerSelected.total_options_excl > 0){
							total += this.makerSelected.total_options_incl
						}
						if(this.discount_excl > 0){
							total -= this.discount_incl 
						}

						this.totalNotFormatted = total;

						return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(total/100)

					} else {

						return 0
					}
				}

			}
		},
		methods: {
			applyCoupon(){

				this.couponError = ''

				if(!this.couponName){

					$("#coupon-name").addClass("required-field")

					return false

				} else {

					$("#coupon-name").removeClass("required-field")

				}

				this.processBtnApply = true

				var data = {
					"customer_id": this.user3dm.id,
					"coupon": this.couponName,
					"total_amount_tax_incl" : this.makerSelected.price_incl + this.makerSelected.total_options_incl + this.shippingSelected.price + this.fees.fees_incl,
					"total_amount_tax_excl" : this.makerSelected.price_excl + this.makerSelected.total_options_excl + this.shippingSelected.price + this.fees.fees_excl,
					"production_amount_tax_incl" : this.makerSelected.price_incl + this.makerSelected.total_options_incl,
					"production_amount_tax_excl" : this.makerSelected.price_excl + this.makerSelected.total_options_excl,
					"shipping_amount_tax_incl" : this.shippingSelected.price,
					"shipping_amount_tax_excl" : this.shippingSelected.price,
					"fee_amount_tax_incl" : this.fees.fees_incl,
					"fee_amount_tax_excl" : this.fees.fees_excl,
				};

		    	this.$http.post(this.apiCoupon, data ).then((response) => 
				{
					console.log('API Coupon => success',response)

						var data = JSON.parse(response.body)

						store.commit('ADD_COUPON',{'name': data.coupon_label,'discount_excl':data.discount_amount_tax_excl, 'discount_incl': data.discount_amount_tax_incl,'code': this.couponName })

						this.processBtnApply = false
						this.couponApply = true

				}, (response) => {

					//console.log('API Combination => error',response)

					store.commit('REMOVE_COUPON')

					this.processBtnApply = false
					this.couponApply = false

					if(response.status === 404){

						$("#coupon-name").addClass("required-field")
						this.couponError = 'Coupon non valide ou non applicable pour cet achat'
					}

				})


			},
			deleteCoupon(){

				this.couponApply = false
				store.commit('REMOVE_COUPON')

			},
			validateSummary(){

				if(this.instruction != ''){

					store.commit('ADD_INSTRUCTION',this.instruction)

				}

				if(this.type == 'design'){

					store.commit('CHANGE_STEP_PROJECT',8)
					setTimeout(function() {$('html,body').animate({scrollTop: $('#payment').offset().top},'slow');}, 500);
					// Google Tag Manager : push event Maker selected (Print)
					//******************************************** */
					gtag_report_event(this.user3dm,'project_form','project_form.order_details.confirm')		
					//******************************************** */

				} else if(this.type == 'basket'){

					store.commit('CHANGE_STEP',3)
					setTimeout(function() {$('html,body').animate({scrollTop: $('#payment').offset().top},'slow');}, 500);

				} else {

					store.commit('CHANGE_STEP',7)
					setTimeout(function() {$('html,body').animate({scrollTop: $('#upload-file').offset().top},'slow');}, 500);
					// Google Tag Manager : push event Maker selected (Print)
					//******************************************** */
					gtag_report_event(this.user3dm,'impression_form','impression_form.order_details.confirm')		
					//******************************************** */
				}

				store.commit('SET_TOTAL_TTC',this.totalNotFormatted);

			}
			
		},
		filters: {
		  pricing: function (value) {

		  	return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format((value)/100)
		    
		  }
		},
		watch:{
			stepFormProcess : function(val){

				if(val < 6 ){

					this.couponApply = false

				}

			}
		}
	}
</script>

<style>

</style>