<template>
	<div>
		<div class="col-sm-12 bg-white text-center" v-if="currentBasket.length < 1">
			<h2>Votre panier</h2>

			<div>
				<p><br>Ce panier n'est effectif que pour pour l'achat de modèle 3D</p>

				<p>Vous navez actuellement aucun modèle dans votre panier.</p>

				<a :href="modelUrl" class="btn btn-default btn-rounded">Chercher des modèles</a>
			</div>
		</div>
		<div v-else id="basket" class="col-sm-12 bg-white pad-15">
            <h2>
				<span class="rounded">1</span>

            	 Votre panier</h2>
				
			<table class="table table-hover">
				<thead>
					<tr>
						<th scope="col" width="130px">Image</th>
						<th scope="col">Nom du modèle</th>
						<th scope="col">Maker</th>
						<th scope="col">Prix</th>
						<th scope="col"></th>
					</tr>
				</thead>
				<tbody v-for="(item, index) in currentBasket">
						<tr>
							<td rowspan="2"><img :src="item.image" class='card-img-basket'></td>
							<td>
								<a :href="modelUrl+item.url">
									{{ item.name }}
								</a>
							</td>
							<td>
								{{ item.makerName}}
							</td>
							<td>
								{{ item.priceTaxIncl }} € TTC
								<br>
								<p class="basketHT">({{ item.priceTaxExcl }} € HT)</p>
							</td>
							<td class="text-right">
								<a v-on:click="deletItem(index,item)">
									<span class="glyphicon glyphicon-trash"></span>
								</a>
							</td>
						</tr>
						<tr>
							<td colspan="4">
								<p v-if="item.caracteristique.length > 150" :id="index" class="longtext">
									<u>Caractéristique :</u>
										&nbsp;&nbsp;&nbsp;
										<caracteristique>
											{{ item.shortCaracteristique }}
											...
										</caracteristique>
										&nbsp;&nbsp;&nbsp;
									
									<a v-on:click="allCaracteristique(index,item.caracteristique, item.shortCaracteristique)">
										<caracteristiquePlus>
											<span class="glyphicon glyphicon-plus"></span>
										</caracteristiquePlus>
									</a>
								</p>
								<p v-else class="longtext">
									<u>Caractéristique :</u>&nbsp;&nbsp;&nbsp;{{ item.caracteristique }}
								</p>
							</td>
						</tr>
				</tbody>
			</table>
			<div class="col-sm-12 text-right" style="margin-top:20px;">
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
		name: "basketFile",
		store: store,
		props: [
			'apiBasket',
			'modelUrl',
			'type',
		],
		data: function(){
			return {
				instruction :'',
				longCaracteristique : [],
			}
		},
		mounted (){
			this.Basket()

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
			]),
			total: function(){
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

					return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(total/100)

				} else {

					return 0
				}

			}
		},
		methods: {
			Basket : function(){
				this.$http.post(this.apiBasket ).then((response) => 
				{
					console.log(this.modelUrl);
					console.log('API Basket => success',response)
					var len = response.body.data.length;
					console.log(len);
					for (var i = 0; i < len; i++) {
						this.longCaracteristique.push(false);
					}

					var data = response.body.data
					store.commit('UPDATE_CURRENT_BASKET', data)
					//this.accountState = 3
					//this.errorLogin = false
	  				//setTimeout(function() {$('html,body').animate({scrollTop: $('#address').offset().top},'slow');}, 500);
				}, (response) => {
					console.log('API User Login => error',response)
					//this.accountState = 0
				})
			},
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
			deletItem(index, item){
				console.log('index',index);
				console.log('item',item.id);
				store.commit('DELETE_ITEM_BASKET', index);

				$("#dynamicBasket searchCategory").show();
				$("#dynamicBasket searchCategory").empty();
				$("#dynamicBasket searchCategory").append('Mon panier (...)');
				$.ajax({
					url:'/fr/api/model/basket/remove',
					type: "POST",
					dataType: "json",
					data: {
						"model_id": item.id,
					},
					async: true,
					success: function (response)
					{
						$.ajax({
							url:'/fr/api/model/basket',
							type: "POST",
							dataType: "json",
							data: {

							},
							async: true,
							success: function (response)
							{
								//console.log('basket : ',response)
								var len = response.data.length;
								//console.log(len);
								//$("#dynamicBasket searchCategory").empty();
								if(len == 0){
									$("#dynamicBasket searchCategory").hide();
									//$("#dynamicBasket searchCategory").append('Mon panier');
								} else {
									$("#dynamicBasket searchCategory").show();
                                    $("#dynamicBasket searchCategory").empty();
									$("#dynamicBasket searchCategory").append('Mon panier (',len,')');
								}
								
							}
						})
					}
				})
				
			},
			validateSummary(){

				if(this.instruction != ''){

					store.commit('ADD_INSTRUCTION',this.instruction)

				}
				store.commit('CHANGE_STEP',8 )

				if(this.type === 'basket') {
					setTimeout(function() {$('html,body').animate({scrollTop: $('#payment').offset().top},'slow');}, 200);
				}
				
			},
			allCaracteristique(index, caracteristique, shortCaracteristique){
				//console.log(caracteristique);
				var divId = "#"+index+ " caracteristique";
				var divButton = "#"+index+ " caracteristiquePlus";
				if(this.longCaracteristique[index] == true) {
					this.longCaracteristique[index] = false;
					//console.log(this.longCaracteristique);
					$(divId).empty();
					$(divId).append(shortCaracteristique);
					$(divButton).empty();
					$(divButton).append('<span class="glyphicon glyphicon-plus"></span>');
				} else {
					this.longCaracteristique[index] = true;
					//console.log(this.longCaracteristique);
					$(divId).empty();
					$(divId).append(caracteristique);
					$(divButton).empty();
					$(divButton).append('<span class="glyphicon glyphicon-minus"></span>');
				}
			},
			shortCaracteristique(index, caracteristique){
				//console.log(caracteristique);
				var divId = "#"+index+ " caracteristique";
				this.longCaracteristique[index] = false;
				console.log(this.longCaracteristique);
				$(divId).empty();
				$(divId).append(caracteristique);
				
			}
			
		},
		filters: {
		  pricing: function (value) {

		  	return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format((value)/100)
		    
		  }
		},
		watch:{
		}
	}
</script>

<style>

</style>