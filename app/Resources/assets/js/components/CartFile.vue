<template>
	<div>
		<div id="cart-sumup" class="bg-white pad-15">
            <h2>Votre Panier</h2>
            <div class="row" v-if="type != 'design' && type !='basket'">
            	<span class="col-sm-7">Impression </span>
            	<span class="col-sm-5 text-right" >{{ printPrice }}</span>
            </div>
            <div class="row" v-show="(makerSelected.total_options_excl > 0 || tmpPriceFinition > 0) && type != 'design'">
            	<div class="col-sm-7">Finition</div>
            	<span class="col-sm-5 text-right">{{ finishingPrice }}</span>
            </div>
            <div class="row" v-if="type == 'design'">
            	<span class="col-sm-7">Prestation</span>
            	<span class="col-sm-5 text-right" >{{ designPrice }}</span>
            </div>
			<div class="row" v-if="type == 'basket'">
            	<span class="col-sm-7">Prix HT</span>
            	<span class="col-sm-5 text-right" >{{ printPriceHT }}</span>
            </div>
            <div class="row" v-show="feesService!=false">
            	<div class="col-sm-7">Frais de service <span class="rounded-info" zb-tooltip="Ces frais couvrent les coûts de fonctionnement de notre plate-forme." zb-tooltip-position="bottom" >?</span>  </div>
            	<span class="col-sm-5 text-right">{{feesService}}</span>
            </div>
            <div class="row" v-show="coupon.name">
            	<div class="col-sm-7">Réduction <span class="rounded-info" :zb-tooltip="coupon.name" zb-tooltip-position="bottom" >?</span>  </div>
            	<span class="col-sm-5 text-right">- {{ couponFormat }}</span>
            </div>
            <div class="row">
            	<span class="col-sm-7">TVA </span>
            	<span class="col-sm-5 text-right">{{ orderVat }}</span>
            </div>
            <div class="row border-bottom" v-show="(shippingSelected.price > 0 || shippingSelected.key == 'pickup') && shipping!=false">
            	<span class="col-sm-7">Frais de port </span>
            	<span class="col-sm-5 text-right">{{shipping}}</span>
            </div>
            <div class="row">
            	<span class="col-sm-7"><strong>TOTAL TTC</strong></span>
            	<span class="col-sm-5 text-right"><strong>{{total}}</strong></span>
            </div>
    	</div>
    </div>
</template>


<script>

	import Vue from 'vue'
	import { mapGetters } from 'vuex'
	import store from '../stores/PrintStore'


	export default {
		name: "cartFile",
		store: store,
		props: [
			'apiFees',
			'type',
		],
		data: function(){
			return {
				tmpVat : 0,
				tmpFees : 0
			}
		},
		mounted (){


		},
		computed: {
			...mapGetters([
				'print3dFiles',
				'makersList',
				'makerSelected',
				'shippingSelected',
				'fees',
				'stepFormProcess',
				'stepFormProject',
				'tmpPrice',
				'tmpPriceFinition',
				'coupon',
				'discount_excl',
				'discount_incl',
				'currentBasket',
			]),
			couponFormat: function(){

				if(this.discount_excl){

					return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format((this.discount_excl)/100)

				} else {

					return 0
				}

			},
			printPrice : function(){

				if(this.stepFormProcess <= 2){

					return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(this.tmpPrice/100)

				}


				if(this.makerSelected.price_excl > 0){

					return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(this.makerSelected.price_excl/100)
				} else {

					return '-'
				}
			},
			designPrice : function(){

				if(this.stepFormProject <= 2){

					return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(this.tmpPrice/100)

				}


				if(this.makerSelected.price_excl > 0){

					return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(this.makerSelected.price_excl/100)
				} else {

					return '-'
				}
			},
			printPriceHT : function(){
				var price = 0;
				for (var item in this.currentBasket) {
					//console.log(this.currentBasket);
					price += this.currentBasket[item].priceTaxExcl;
				}
				return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(price)

			},
			finishingPrice : function(){

				if(this.stepFormProcess <= 2){

					return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(this.tmpPriceFinition/100)

				}

				if(this.makerSelected.total_options_excl > 0){

					return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(this.makerSelected.total_options_excl/100)

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
					if(this.stepFormProcess <= 2 && this.stepFormProject <= 2){

						let vat = (this.tmpPriceFinition+this.tmpPrice+this.tmpFees)*0.2

						return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(vat/100)
						//return '-'
					}

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

						return '-'
					}
				}

				
			},
			feesService : function(){

				if(this.stepFormProcess <= 2 && this.stepFormProject <= 2){
					//return 0
					if(this.tmpPrice > 0){

						return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(this.tmpFees/100)

					} else {

						return 0
					}
				}

				if(this.fees.fees_excl > 0){

					return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(this.fees.fees_excl/100)

				} else {

					return 0
				}

			},
			shipping: function(){

				if(this.stepFormProcess <= 2 && this.stepFormProject <= 2){
					return 0
				}

				if(this.shippingSelected.key == 'pickup'){
					return 'Gratuit'
				} else {
					if(this.shippingSelected.price > 0){
						return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format((this.shippingSelected.price)/100)
					} else {
						return '-'
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
					if(this.stepFormProcess <= 2 && this.stepFormProject <= 2){
						
						let total = (this.tmpPriceFinition+this.tmpPrice+this.tmpFees)*1.2

						return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format((total)/100)

					}

					if(this.makerSelected.price_incl > 0 /*&& this.fees.fees_incl > 0*/){

						let total = 0
						total += this.makerSelected.price_incl
						total += this.fees.fees_incl

						if(this.makerSelected.total_options_excl > 0){
							total += this.makerSelected.total_options_incl

						}
						if(this.shippingSelected.key!= 'pickup' && !isNaN(this.shippingSelected.price)){
							total += this.shippingSelected.price
						}

						if(this.discount_excl > 0){
							total -= this.discount_incl 
						}

						return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(total/100)

					} else {

						return '-'
					}
				}

				

			}

		},
		methods: {
			calculateFees(value){
				var tagSpec = null
				if ((this.type == 'design') && (projectOrigin != null)){
					this.originalProject = JSON.parse(projectOrigin)
					tagSpec=this.originalProject.type[0].tagSpec
				} 

				//Calculate Fees
				let data = {
					'amount': value , 
					'taggSpec' : tagSpec
					}

				self = this
				
				this.$http.post(this.apiFees, data ).then((response) => 
				{
					
					var data = JSON.parse(response.body)

					self.tmpFees = data.fee_tax_excl

				}, (response) => {

					console.log('API Shipping => error',response)

				})

			}
		},
		watch: {
			total: function(value){

				if(this.stepFormProcess <= 2){

					this.calculateFees(this.tmpPrice + this.tmpPriceFinition)

				}
			}
		},
	}
</script>

<style>
	
</style>