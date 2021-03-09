<template>
	<div>
	<!-- <div class="col-sm-12">
            <h1>Frame 3 - 3D File {{productId}}</h1>
    </div> -->
	<div>

		<input type="file" class="custom-file-input" :id="productId" name="files[]" @click="clickSearchFile"  @input="initFile" v-show="printFile.state=='open'" accept=".stl, .obj"/>

		<!-- <label class="custom-file-label" for="fileId">Sélectionnez votre fichier</label> -->
		<!-- <button id="btn-upload">Upload</button> -->
		
		



		<div id="canvas-drop" class="col-sm-7 canvasBack">
			<div id="view3d">
				<div :id="'drop-zone'+productId" class="middle" v-show="stepFile == 0">
					<div>
						<p class="h3">Déposer votre fichier (.stl ou .obj)<br> ou</p>
						<label :for="productId" class="btn">Recherche</label>
					</div>
				</div>
				<canvas id="cv" width="400" height="300"></canvas>
				<div class="col-sm-12 pad-15-0" v-if="stepFile > 0">

					<div v-if="printFile.state=='open'">
						<div>
							Dimensions :
						</div>
						<input v-model="displayX" type="number" v-on:change="recalculateDimension('x')" class="col-sm-3" min="0">
						<span class="col-sm-1">
							<!-- {{ selectedUnity }} --> 
							x 
						</span>
						<input v-model="displayY" type="number" v-on:change="recalculateDimension('y')" class="col-sm-3" min="0">
						<span class="col-sm-1">
							<!-- {{ selectedUnity }} --> 
							x 
						</span>
						<input v-model="displayZ" type="number" v-on:change="recalculateDimension('z')" class="col-sm-3" min="0">
						<span class="col-sm-1">
							{{ selectedUnity }}
						</span>
					</div>
					<div v-else class="col-sm-7">
						<span>
							Dimensions :
						</span>
						<span>{{displayX}}</span> 
						<span>
							<!-- {{ selectedUnity }} --> 
							x 
						</span>
						<span>{{displayY}}</span>
						<span>
							<!-- {{ selectedUnity }} --> 
							x 
						</span>
						<span>{{displayZ}}</span>
						<span>
							{{ selectedUnity }}
						</span>
					</div>

					<div class="col-sm-5 pad-0">
						
						Volume : {{ printFile.volumeDisplay }} cm <!-- {{ selectedUnity }} --> <sup>3</sup> 
						<!-- Nombre de partie : {{ printFile.numberOfParts }} -->
					</div>
				</div>
			</div>
		</div>

		<div class="alert-danger pad-10 col-sm-4 text-center" v-if="errorFileFormat" style="margin-left:30px;margin-top:172px;">
			Merci d'uploader un fichier Obj ou Stl
		</div>
		
		<div class="alert-danger pad-10 col-sm-4 text-center" v-if="error == 'no-maker'" style="margin-left:30px;margin-top:172px;">
			Pas de maker pour la taille et/ou la quantité demandée
		</div>

		<div id="col-right-parameter" class="col-sm-5" v-show="stepFile > 0 && error != 'no-maker'">

			<div id="parameter col-sm-12">


				<div class="col-sm-12  pad-0 text-right" v-if="priceFromDisplay">
					<p>Prix unitaire</p>
					<h3>A partir de {{ priceFromDisplay }} HT</h3>
					<p>{{ priceFromTtcDisplay }} TTC</p>
				</div>


				<div>
					<span class="col-sm-5 pad-0">Nom du fichier :</span>
					<p class="col-sm-7">
						{{ printFile.filename }}
					</p>
				</div>
				

				<div>
					<span class="col-sm-5 pad-0">Quantité</span>
					<span class="col-sm-7">
						<input class="col-sm-12" type="number" v-model="printFile.quantity" :required="true" @input="apiCombination(null,null,null,null,null,null)" @change="apiCombination(null,null,null,null,null,null)" v-if="printFile.state=='open'" min="1" step="1">
						<p v-else class="mrg-0">{{ printFile.quantity }}</p>
					</span>
				</div>
				<!-- Step Technologies -->
				<!-- <div v-if="optionsTechnology.length > 0"> -->
				<div>
					<span class="col-sm-5 pad-0">Technologie
						<div class="infosbull" v-if="printFile.technology > 0 && (this.refTechnology[printFile.technology].description || this.refTechnology[printFile.technology].image)">
							<span class="rounded-info" >?</span>
							<div v-html="((refTechnology[printFile.technology].image) ? refTechnology[printFile.technology].image : '')  + ((refTechnology[printFile.technology].description) ? refTechnology[printFile.technology].description : '')"></div>
						</div>
					</span>
					<span class="col-sm-7">
						<select name="technology" id="technology" v-model="printFile.technology" :required="true" @change="makeMaterialSelect" v-if="printFile.state=='open'">
							<option v-for="technology in optionsTechnology" v-bind:value="technology.value">
			    				{{ technology.text }}
			  				</option>
						</select>
						<p v-else class="mrg-0">{{ this.refTechnology[printFile.technology].name }}</p>
					</span>
				</div>
				<!-- END Step Technologies -->
				<!-- Step Materials -->
				<div>
					<span class="col-sm-5 pad-0">Matériau
						<div class="infosbull" v-if="printFile.material > 0 && (this.refMaterial[printFile.material].description || this.refMaterial[printFile.material].image)">
							<span class="rounded-info" >?</span>
							<div v-html="((refMaterial[printFile.material].image) ? refMaterial[printFile.material].image : '')  + ((refMaterial[printFile.material].description) ? refMaterial[printFile.material].description : '')"></div>
						</div>
					</span>
					<span class="col-sm-7" v-if="printFile.state=='open'">
						<select name="material" id="material" v-model="printFile.material" :required="true" @change="makeColorSelect">
							<option v-for="material in optionsMaterial" v-bind:value="material.value">
			    				{{ material.text }}
			  				</option>
						</select>
					</span>
					<span class="col-sm-7" v-else>
						<p class="mrg-0">{{ this.refMaterial[printFile.material].name }}</p>
					</span>
				</div>
				<!-- END Step Materials -->
				<!-- Step Colors -->
				<div>
					<span class="col-sm-5 pad-0">Couleur
						<div class="infosbull" v-if="printFile.color > 0 && (refColor[printFile.color].description || refColor[printFile.color].image)">
							<span class="rounded-info" >?</span>
							<div v-html="((refColor[printFile.color].image) ? refColor[printFile.color].image : '')  + ((refColor[printFile.color].description) ? refColor[printFile.color].description : '')"></div>
						</div>
					</span>
					<span class="col-sm-7">
						<select name="color" id="color" v-model="printFile.color" :required="true" @change="makeLayerSelect" v-if="printFile.state=='open'">
							<option v-for="color in optionsColor" v-bind:value="color.value">
			    				{{ color.text }}
			  				</option>
						</select>
						<p v-else class="mrg-0">{{ this.refColor[printFile.color].name }}</p>
					</span>
				</div>
				<!-- END Step Colors -->
				<!-- Step Layers -->
				<div>

					<span class="col-sm-5 pad-0">Hauteur de couche 
						<div class="infosbull">
							<span class="rounded-info" >?</span>
							<div>
								<div v-html="((refLayer[printFile.layer].image) ? refLayer[printFile.layer].image : '')" v-if="printFile.layer > 0 &&  refLayer[printFile.layer].image"></div>
								La hauteur de couche mesure l'épaisseur <br>
								de chaque ajout de matière. <br> <br>
	 
								Pour exemple :<br>
								Inférieur à 50 µm : Très Haute Définition<br>
								Entre 50 µm et 100 µm : Haute Définition<br>
								Entre 150 µm et 250 µm : Définition Standard<br>
								Supérieur à 300 µm : Basse Définition<br><br>

								Dépendant de la technologie d'impression 3D utilisée.
							</div>
						</div>
					</span>


					<span class="col-sm-7">
						<select name="layer" id="layer" v-model="printFile.layer" :required="true" @change="makeFillingSelect" v-if="printFile.state=='open'">
							<option v-for="layer in optionsLayer" v-bind:value="layer.value">
			    				{{ layer.text }}
			  				</option>
						</select>
						<p v-else>{{ this.refLayer[printFile.layer].name }}</p>
					</span>
				</div>
				<!-- END Step Layers -->
				<!-- Step Layers -->
				<div v-if="hasFillingRate == true">
					<span class="col-sm-5 pad-0">Remplissage
						<div class="infosbull">
							<span class="rounded-info" >?</span>
							<div>
								Un remplissage à 0% correspond à un objet "vide", juste formé de sa coque. <br>
								Un objet imprimé à 100% est totalement "plein" <br><br>

								Le taux de remplissage par défaut est de 20-30%.<br>
								Ce taux de remplissage type est un bon compromis entre solidité, matière première utilisée et temps d'impression.
							</div>
						</div>
					</span>
					<span class="col-sm-7">
						<select name="filling" id="filling" v-model="printFile.filling" :required="true" @change="makeFinishingCheckbox" v-if="printFile.state=='open'">
							<option v-for="filling in optionsFilling" v-bind:value="filling.value">
			    				{{ filling.text }}
			  				</option>
						</select>
						<p v-else class="mrg-0">{{ this.refFilling[printFile.filling].name }}</p>
					</span>
				</div>
				<!-- END Step Layers -->
				<!-- Step Finishing -->
		    	<div class="col-sm-12" v-show="(displayFinishing && printFile.state=='open') || (printFile.finishing.length > 0)">
	    			<div class="underline pointer" @click="toogleViewBlockFinishing()">+ d'options</div>
			    	<div v-show="viewBlockFinishing">
			    		<div v-for="(finishing, index) in checkboxFinishing" :key="finishing.id" v-show="printFile.state=='open'">
							<label>
					            <input type="checkbox" :id="finishing.title" :data-id="finishing.id" :name="finishing.title" :value="finishing" v-model="printFile.finishing">
					            <span></span>
					            <span class="wrapped-label">{{finishing.title}}
					            	<div class="infosbull" v-if="finishing.description != undefined">
										<span class="rounded-info" >?</span>
										<div v-html="finishing.description"></div>
									</div>
					            </span>
					        </label>
					        <br>
				    	</div>
				    	<div class="col-sm-12" v-show="printFile.state=='lock'">
				    		<div v-for="finishingChecked in printFile.finishing" :key="finishingChecked.id" class="mrg-0">
					    		<i class="fas fa-check-square"></i>
					    		<span>{{ finishingChecked.title }}</span>
				    		</div>
				    	</div>
			    	</div>
		    	</div>

			</div>
		</div>
	</div>


	<div id="bottom-parameter" class="col-sm-12"  v-if="stepFile > 0">
		<div class="col-sm-5 mrg-t-20">

			<label for="list-unit" class="col-form-label col-sm-5 pad-5">Unité du modèle <span class="rounded-info" zb-tooltip="Attention à bien indiquer l'unité de votre modèle !" zb-tooltip-position="bottom" >?</span>  : </label>

			<div class="col-sm-7 pad-0">
				<select class="unit-select" name="list-unit" id="unit" v-model="selectedUnity" v-show="printFile.state=='open'">
					<option v-for="option in optionsUnity" v-bind:value="option.value">
	    				{{ option.text }}
	  				</option>
				</select>
				<div class="col-sm-12 col-form-label" v-show="printFile.state=='lock'">
					{{selectedUnity}}
				</div>
			</div>
		</div>
    	<!-- END Step Finishing -->
    	<div class=" text-right col-sm-12">		    		
			<span class="underline pad-0-15" v-show="lastProduct == true" @click="deleteProduct">Supprimer cet objet</span>    	
			<button class="btn btn-default btn-rounded" @click="validateProduct" v-show="printFile.state=='open' && errorSize!=true">Valider cet objet</button>
			<button class="btn btn-default btn-rounded" @click="editProduct" v-show="printFile.state=='lock' && lastProduct == true">Modifier cet objet</button>
		</div>
	</div>
	<div id="bottom-parameter" class="col-sm-12"  v-if="stepFile == 0 && lastProduct == true && productId > 0">
		<div class=" text-right col-sm-12">		    		  	
			<span class="underline pad-0-15" @click="deleteProduct">Supprimer cet objet</span>
		</div>

	</div>

    </div>
</template>


<script>
	
	import Vue from 'vue'
	import { mapGetters } from 'vuex'
	import store from '../stores/PrintStore'


	export default {
		name: "printFile",
		store: store,
		props: [
			'apiPricing',
			'productId',
		],
		data: function(){
			return {
				stepFile: 0,
				printFile: {
					file: null,
					filename: null,
					extension: null,
					dimensions: {
						x: null,
						y: null,
						z: null
					},
					volume: null,
					volumeDisplay: null,
					weight: null,
					density: null,
					color: '#760039',
					finishing: [],
					finishingChecked: [],
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
					//maker: null,
					priceTaxEcl: 0,
					priceTaxInc: 0,
					state: 'open', // open / lock 
				},
				displayDefinition : 'standard',
				selectedUnity : 'mm',
				optionsUnity : [
					{ text: 'mm', value: 'mm'},
					{ text: 'cm', value: 'cm'},
					{ text: 'm', value: 'm'},
					{ text: 'inch', value: 'inch'}
				],
				colorBg1 : "#CFCFCF",
				colorBg2 : "#111111",
				colorDefault: "#760039",
				convertUnity : [ [1,0.1,0.01,25.4], [10,1,0.1,2.54],[100,10,1,0.254],[0.0393701,0.393701,3.93701,1]],
				rounded : 2,
				coefDim: 1,
				sizeOriginX: null,
				sizeOriginY: null,
				sizeOriginZ: null,
				displayX: null,
				displayY: null,
				displayZ: null,
				volumeOrigin: null,
				viewer: null,
				hasFillingRate:false,
				combinations: {},
				makersDisplay: {},
				refTechnology: {},
				refMaterial: {},
				refColor: {},
				refLayer: {},
				refFinishing: {},
				refFilling: {},
				optionsTechnology:[],
				optionsMaterial:[],
				optionsColor:[],
				optionsLayer:[],
				optionsFilling:[],
				checkboxFinishing: [],
				displayFinishing: false,
				viewBlockFinishing: false,
				priceFrom: null,
				/*priceFromDisplay:null,*/
				error: null,
				helpMaterial: null,
				helpMaterialLink: null,
				stateFinishing:0,
				fileInput:null,
				errorFileFormat: 0,

				tmpPrice:0,
				tmpPriceFinition:0,

				errorSize:false,

			}
		},
		mounted (){
			this.viewer = new JSC3D.Viewer(this.$el.querySelector('#cv'))
			this.viewer.setParameter('ModelColor', this.printFile.color )
			this.viewer.setParameter('BackgroundColor1', this.colorBg1 )
			this.viewer.setParameter('BackgroundColor2', this.colorBg2 )
			this.viewer.setParameter('Render', 'webgl')



			//console.log('productId => ', this.productId)

			let canvas_drop = document.getElementById('drop-zone'+this.productId)
			let fileInput = document.getElementById(this.productId);

			var self = this

		    canvas_drop.addEventListener("dragenter", function (e) {
		    	//console.log('dragenter')
		    	canvas_drop.style.border = "4px solid #760039";
		    });

		    canvas_drop.addEventListener("dragleave", function (e) {
		    	//console.log('dragleave')
		    	e.preventDefault()
		    	canvas_drop.style.border = "none";  
		    });

		    canvas_drop.addEventListener("dragover", function (e) {
		    	//console.log('dragover')
		        e.preventDefault()
		        canvas_drop.style.border = "4px solid #760039";
		    });

		    canvas_drop.addEventListener("drop", function (e) {
		    	//console.log('drop')
		        e.preventDefault()
		        canvas_drop.style.border = "none";

		        fileInput = e.dataTransfer.files[0]

		        //console.log('TYPE TYPE',e.dataTransfer.files[0].name)
		        self.fileInput = fileInput
				self.initFile()

		    });

						// Google Tag Manager : push event File upload started
			//******************************************** */
			gtag_report_event(this.user3dm,'impression_form','impression_form.view')
			//******************************************** */

		},
		computed: {
			...mapGetters([
				'print3dFiles',
				'makersList',
				'user3dm',
				'stepFormProcess',
				'fileNumber'
			]),
			priceFromDisplay : function(){

				return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(this.priceFrom)

			},
			priceFromTtcDisplay : function(){

				return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(this.priceFrom*1.2)

			},
			lastProduct : function(){

				this.productId 
				this.fileNumber
				if(this.productId == (this.fileNumber - 1)){
					return true
				} else {
					return false
				}
			},
			quantityComputed : function(){
				return this.printFile.quantity
			},
			finishingComputed : function(){
				return this.printFile.finishing
			}
		},
		methods: {
			toogleViewBlockFinishing(){

				(this.viewBlockFinishing && this.printFile.state == 'open') ? this.viewBlockFinishing = false : this.viewBlockFinishing = true;

			},

			clickSearchFile(event) {

				// Google Tag Manager : push event File upload started
				//******************************************** */
				gtag_report_event(this.user3dm,'impression_form','impression_form.upload.started')
				//******************************************** */
			},

			
			initFile(event) {

				let fileData = document.getElementById(this.productId).files[0];

				if(fileData == undefined){
					fileData = this.fileInput
				}

				//console.log('FILEDATA',fileData)
				//var fileData =  event.target.files[0]
				this.printFile.file = fileData
				var fileName =  fileData.name
				var fileExtension = fileName.split('.').pop().toLowerCase()

				if(fileExtension == 'obj' || fileExtension == 'stl' ){
					this.stepFile = 1
					this.printFile.filename = fileName
					this.printFile.extension = fileExtension
					this.errorFileFormat = 0

				    this.preview_stl(fileData)
					
					// Google Tag Manager : push event File upload complete
					//******************************************** */
					gtag_report_event(this.user3dm,'impression_form','impression_form.upload.complete')
					//******************************************** */


					

				} else {
					// Display alert div
					this.errorFileFormat = 1
					//this.stepFile = -1
				}

   				//console.log('File',event.target.files[0])
   				//console.log('Filename', fileData.name);
   				//console.log('Extensions', fileData.name.split('.').pop())
				
			},
	    	precisionRound(number, precision) {
		        var factor = Math.pow(10, precision)
		        return Math.round(number * factor) / factor
		    },

			gtagInfofile (){
				var gtagInfoFile = {
					"dimension":this.printFile.dimensions,
					"volumeDisplay":this.printFile.volumeDisplay,	
					"fileName":this.printFile.filename,
					"extension":this.printFile.extension,
				}	
				gtag_report_event(this.user3dm,'impression_form','file3D_upload',gtagInfoFile)
			},
		    calculDimension(scene){

		    	var parent = this

				if (scene !== null && scene.getChildren().length > 0) {

            		//console.log('Calcul Dimensions methods VUE')
					var aabb = scene.aabb
					this.printFile.volume = 0;
					this.printFile.numberOfParts = 0;

					scene.forEachChild( function(mesh) {
					//totalVolume += computeVolume(mesh);
						parent.printFile.volume += parent.computeVolume(mesh)
						parent.printFile.numberOfParts += 1;
					} );

					this.printFile.volume = this.precisionRound(this.printFile.volume, this.rounded)
					this.printFile.volumeDisplay = Math.ceil(this.printFile.volume/1000)

					//RealX = aabb.maxX - aabb.minX
					this.printFile.dimensions.x = this.precisionRound((aabb.maxX - aabb.minX)*this.coefDim,this.rounded)
					this.displayX = this.sizeOriginX = this.printFile.dimensions.x

					//RealY = aabb.maxY - aabb.minY
					this.printFile.dimensions.y = this.precisionRound((aabb.maxY - aabb.minY)*this.coefDim,this.rounded)
					this.displayY = this.sizeOriginY = this.printFile.dimensions.y

					//RealZ = aabb.maxZ - aabb.minZ
					this.printFile.dimensions.z = this.precisionRound((aabb.maxZ - aabb.minZ)*this.coefDim,this.rounded)
					this.displayZ = this.sizeOriginZ = this.printFile.dimensions.z
					this.volumeOrigin = this.printFile.volume

					this.apiCombination('10','15','20','5000','1',null)
					
					this.gtagInfofile ()	


				}

		    },
			preview_stl(f) {

				//console.log('Preview STL ', f)
    			var theScene = new JSC3D.Scene
    			var stlpath = ""//"../../../assets/2013-10-23/stl/box.STL"
    			this.viewer.setParameter('SceneUrl', stlpath)
				this.viewer.setParameter('InitRotationX', 20)
				this.viewer.setParameter('InitRotationY', 20)
				this.viewer.setParameter('InitRotationZ', 0)
				this.viewer.init()
				this.viewer.resetScene()

				var parent = this

				this.viewer.calculDimension = function(scene){

					parent.calculDimension(scene)

				}

			    var reader = new FileReader()
			    var ext = f.name.split(".")[1]

			    reader.onload = (function(file) {

			        return function(e) {
			        	
			        	var theNewScene = new JSC3D.Scene
			            var extension = ext.toLowerCase()
			            //console.log('Extension => ', extension)

			            if(extension == 'stl'){

			            	//console.log('File STL')
			                var stl_loader = new JSC3D.StlLoader()
			                stl_loader.parseStl(theNewScene, e.target.result)

			            } else if (extension == 'obj'){

			            	//console.log('File OBJ')
			                var obj_loader = new JSC3D.ObjLoader()
			                obj_loader.parseObj(theNewScene, e.target.result)

			            }

			            parent.viewer.replaceScene(theNewScene)
			            parent.viewer.resetScene()
			            parent.viewer.update()
	     
			        }
				})(f)
			    reader.readAsBinaryString(f)
			    reader.onloadend = function(){
			    	var scene = parent.viewer.getScene()
			    	parent.viewer.calculDimension(scene);

			    }
	    	},
	    	computeVolume(mesh) {
		        var sum = 0;
		        var ibuf = mesh.indexBuffer;
		        var vbuf = mesh.vertexBuffer;
		        var i = 0, j = 0;
		        // walk through all faces, calculating the volume of the mesh 
		        while(i < mesh.faceCount) {
		          var v0, v1, v2;
		          var x0, y0, z0, x1, y1, z1, x2, y2, z2;
		          v0 = ibuf[j++] * 3;
		          v1 = ibuf[j++] * 3;
		          // calculate volume of the polyhedron formed by the origin point and this face
		          do {
		            v2 = ibuf[j++] * 3;
		            x0 = vbuf[v0    ];
		            y0 = vbuf[v0 + 1];
		            z0 = vbuf[v0 + 2];
		            x1 = vbuf[v1    ];
		            y1 = vbuf[v1 + 1]; 
		            z1 = vbuf[v1 + 2];
		            x2 = vbuf[v2    ];
		            y2 = vbuf[v2 + 1];
		            z2 = vbuf[v2 + 2];
		            sum += - x2 * y1 * z0
		                   + x1 * y2 * z0 
		                   + x2 * y0 * z1
		                   - x0 * y2 * z1
		                   - x1 * y0 * z2
		                   + x0 * y1 * z2;
		            v1 = v2;
		          } while (ibuf[j] != -1);
		          // continue to next face
		          j++;
		          i++;
		        }
		       // console.log('Vol =>',Math.abs(sum/6))

		        return Math.abs(sum/6);
		    },
		    recalculateDimension(axeChange){

		    	//console.log('recalculateDimension')
		    	//Coef by unity
		    	let coef = 1
		    	if(this.selectedUnity == 'mm'){
		    		coef = 1
		    	} else if(this.selectedUnity == 'cm'){
		    		coef = 10
		    	} else if(this.selectedUnity == 'm'){
		    		coef = 1000
		    	} else if(this.selectedUnity == 'inch'){
		    		coef = 25.4
		    	}


		    	// Definition coef 
		    	if(axeChange == 'x'){
		    		this.coefDim = this.displayX / this.sizeOriginX 
		    		this.printFile.dimensions.x = this.displayX*coef
		    		this.printFile.dimensions.y = this.precisionRound(this.sizeOriginY*this.coefDim*coef,this.rounded)
		    		this.displayY = this.precisionRound(this.sizeOriginY*this.coefDim,this.rounded)
		    		this.printFile.dimensions.z = this.precisionRound(this.sizeOriginZ*this.coefDim*coef,this.rounded)
		    		this.displayZ = this.precisionRound(this.sizeOriginZ*this.coefDim,this.rounded)

		    	} else if(axeChange == 'y'){
		    		this.coefDim = this.displayY / this.sizeOriginY
		    		this.printFile.dimensions.y = this.displayY*coef
		    		this.printFile.dimensions.x = this.precisionRound(this.sizeOriginX*this.coefDim*coef,this.rounded)
		    		this.displayX = this.precisionRound(this.sizeOriginX*this.coefDim,this.rounded)
			    	this.printFile.dimensions.z = this.precisionRound(this.sizeOriginZ*this.coefDim*coef,this.rounded)
			    	this.displayZ = this.precisionRound(this.sizeOriginZ*this.coefDim,this.rounded) 

		    	} else if(axeChange == 'z'){
		    		this.coefDim = this.displayZ / this.sizeOriginZ 
		    		this.printFile.dimensions.z = this.displayZ*coef
		    		this.printFile.dimensions.x = this.precisionRound(this.sizeOriginX*this.coefDim*coef,this.rounded)
		    		this.displayX = this.precisionRound(this.sizeOriginX*this.coefDim,this.rounded)
		    		this.printFile.dimensions.y = this.precisionRound(this.sizeOriginY*this.coefDim*coef,this.rounded)
		    		this.displayY = this.precisionRound(this.sizeOriginY*this.coefDim,this.rounded)
		    	} else {

		    		this.printFile.dimensions.x = this.displayX*coef
		    		this.printFile.dimensions.y = this.displayY*coef
		    		this.printFile.dimensions.z = this.displayZ*coef

		    	}


		    	let indice = 1
		    	if(this.selectedUnity == 'mm'){
		    		indice = 1
			    } else if(this.selectedUnity == 'cm'){
			    	indice = 1000
			    } else if(this.selectedUnity == 'm'){
			    	indice = 1000000000
			    } else if(this.selectedUnity == 'inch'){
			    	indice = 16387.064
			    }


		    	this.printFile.volume = this.precisionRound((this.volumeOrigin*indice)*(Math.pow(this.coefDim, 3)),this.rounded)

				this.printFile.volumeDisplay = Math.ceil(this.printFile.volume/1000)
				
				

				//console.log('New volume =>',this.printFile.volume)
				//console.log('New volume Display =>',this.printFile.volumeDisplay)

		    	this.apiCombination('10','15','20','5000','1',null)
				this.gtagInfofile ()	
		    },
		    changeColor(hexacolor){
		    	
		    	let colorFormatted = '0x'+hexacolor.substring(1,7)

		    	this.viewer.getScene().forEachChild( function(mesh){
		    		mesh.setMaterial(new JSC3D.Material('', 0, colorFormatted, 0, true))
		    	})

        		this.viewer.update()

		    },
		    bestPriceCombination(technologiesObject,atechnology,amaterial,acolor,alayer,afilling){

		    	let tempBestCombination = []
		    	let bestCombination = []
		    	let tempPrice = 0 // in cents 
		    	let makersList = []
		    	this.checkboxFinishing = {}
		    	let tempFinishingAr = {} // init finishing array

		    	//Iteration TECHNOLOGY
		    	for (let [k, v] of Object.entries(technologiesObject)) {

		    		if(atechnology && atechnology!= k){ continue }

		    		//Iteration MATERIAL
		    		for (let [a, b] of Object.entries(v.materials)) {

		    			if(amaterial && amaterial!= a){ continue }

		    			//Iteation COLOR
		    			for (let [c, d] of Object.entries(b.colors)) {

		    				if(acolor && acolor!= c){ continue }

		    				//Iteration LAYER
		    				for (let [e, f] of Object.entries(d.layers)) {

		    					if(alayer && alayer!= e){ continue }

		    					//Iteration FILLINGS
		    					for (let [g, h] of Object.entries(f.fillings)) {

		    						if(afilling && afilling!= g){ continue }

		    						//Iteration PRICES
		    						for (let [i, j] of Object.entries(h.prices)) {


		    							// Finishing complete tab
		    							
			    						if((Object.keys(j.options).length > 0) && this.printFile.finishing.length == 0 ){

			    							//console.log('PARSING OPTIONS DISPONIBLE',i)
			    							for (let [n, o] of Object.entries(j.options)) {

			    								tempFinishingAr[n] = { 'title' : this.refFinishing[n].name , 'id' : n, 'priceht' :  n.option_tax_excl , 'pricettc' : n.option_tax_incl, 'description' : this.refFinishing[n].description, 'link' : this.refFinishing[n].link }

			    								//console.log('tempFinishingAr =>',tempFinishingAr)
			    						
			    							}
			    						} else {




			    						}

		    							//Finishing
		    							//let optionFromPrice = 0
		    							let optionFromPrice = 0
		    							if(this.printFile.finishing.length > 0){

											//console.log('BEST PRICE FINISHING PROCESS',this.printFile.finishing.length)

											let optionsToFind = Object.keys(this.printFile.finishing).length
											let optionsFound = 0
											

											if(Object.keys(j.options).length > 0){

												for(let option in this.printFile.finishing){
													
													for(let [l, m] of Object.entries(j.options)){

														//console.log('var l iteration', l)
														//console.log('var m iteration', m)
														//console.log('iteration this.printFile.finishing[option].id', this.printFile.finishing[option].id)

														if(l == this.printFile.finishing[option].id){
															//optionFromPrice += (this.printFile.finishing[option].priceht)/this.printFile.quantity
															optionFromPrice += m.option_tax_excl
															//console.log('LLLLL => ',optionFromPrice)
															optionsFound ++

														}

													}

												}

												if(optionsToFind != optionsFound){

													continue

												} else {

													if((Object.keys(j.options).length > 0) && this.printFile.finishing.length > 0 ){

						    							//console.log('PARSING OPTIONS DISPONIBLE',i)
						    							for (let [n, o] of Object.entries(j.options)) {

						    								tempFinishingAr[n] = { 'title' : this.refFinishing[n].name , 'id' : n, 'priceht' :  n.option_tax_excl , 'pricettc' : n.option_tax_incl ,'description' : this.refFinishing[n].description, 'link' : this.refFinishing[n].link}

						    								//console.log('tempFinishingAr =>',tempFinishingAr)
						    						
						    							}
						    						}



													//console.log('MAKER WITH OPTION FOUND !!!!')

												}


											} else {

												continue

											}

										}

										//tempPrice += optionFromPrice

		    							if(tempPrice == 0 || ((j.price_tax_excl + optionFromPrice) <= (tempPrice))){

		    								//console.log('JJJJJ => ', j)

		    								tempBestCombination['technology'] = []
		    								tempBestCombination['technology']['label'] = this.refTechnology[k].name 
		    								tempBestCombination['technology']['id'] = k

		    								if(this.refTechnology[k].has_filling_rate){
		    									this.hasFillingRate = true
		    								} else {
		    									this.hasFillingRate = false
		    								}
		    								tempBestCombination['material'] = []
							    			tempBestCombination['material']['label'] = this.refMaterial[a].name
							    			tempBestCombination['material']['id'] = a

							    			//displaying option description
											if(this.refMaterial[a].description){
												this.helpMaterial = this.refMaterial[a].description
											} else {
												this.helpMaterial = false
											}
											if(this.refMaterial[a].link){
												this.helpMaterialLink = this.refMaterial[a].link
											} else {
												this.helpMaterialLink = false
											}

							    			tempBestCombination['color'] = []
						    				tempBestCombination['color']['label'] = this.refColor[c].name
						    				tempBestCombination['color']['id'] = c
						    				tempBestCombination['layer'] = []
					    					tempBestCombination['layer']['label'] = this.refLayer[e].name
					    					tempBestCombination['layer']['id'] = e
					    					tempBestCombination['fillings'] = []
				    						tempBestCombination['fillings']['label'] = this.refFilling[g].name
				    						tempBestCombination['fillings']['id'] = g
				    						tempBestCombination['prices'] = []
			    							tempBestCombination['prices']['priceTaxEx'] = j.price_tax_excl
			    							tempBestCombination['prices']['priceTaxIn'] = j.price_tax_incl
			    							tempBestCombination['maker'] = []
			    							tempBestCombination['maker']['label'] = this.makersDisplay[i].name
			    							tempBestCombination['maker']['id'] = i


		    								tempPrice = j.price_tax_excl

		    								tempPrice = optionFromPrice + tempPrice

		    								this.tmpPrice = j.price_tax_excl
		    								this.tmpPriceFinition = optionFromPrice

		    							} else {
		    								continue
		    							}

		    							/*if((Object.keys(j.options).length > 0) && this.printFile.finishing.length > 0 ){

			    							//console.log('PARSING OPTIONS DISPONIBLE',i)
			    							for (let [n, o] of Object.entries(j.options)) {

			    								tempFinishingAr[n] = { 'title' : this.refFinishing[n].name , 'id' : n, 'priceht' :  n.option_tax_excl , 'pricettc' : n.option_tax_incl }

			    								//console.log('tempFinishingAr =>',tempFinishingAr)
			    						
			    							}
			    						}*/

		    							
		    						}
		    					}
		    				}
		    			}
		    		}
				}

				if(Object.keys(tempFinishingAr).length > 0){

					this.checkboxFinishing = Object.keys(tempFinishingAr).sort(function (a, b) {
						if(tempFinishingAr[a].title < tempFinishingAr[b].title) return -1
						if(tempFinishingAr[a].title > tempFinishingAr[b].title) return 1
						return 0
					}).reduce(
						(acc, id) => 
							[ ...acc, { 
								id, 
								title: tempFinishingAr[id].title, 
								priceht: tempFinishingAr[id].priceht,
								pricettc: tempFinishingAr[id].pricettc,
								description: tempFinishingAr[id].description,
								link: tempFinishingAr[id].link,
							}], {});

					this.displayFinishing = true

				} else {

					this.displayFinishing = false;

				}

				// IT FILL DEFAULT SHIPPEST PRODUCT
				//console.log('tempBestCombination : ', tempBestCombination)
				//console.log('Best Price is :', tempPrice)
				//if( typeof tempBestCombination['technology'] !== 'undefined'){
					//this.makeTechnologiesSelect()
				this.printFile.technology = tempBestCombination['technology']['id']
				this.printFile.technologyLabel = tempBestCombination['technology']['label']
				//}
				if(!atechnology) this.makeMaterialSelect()
				this.printFile.material = tempBestCombination['material']['id']
				this.printFile.materialLabel = tempBestCombination['material']['label']

				if(!amaterial) this.makeColorSelect()
				this.printFile.color = tempBestCombination['color']['id']
				this.changeColor(this.refColor[this.printFile.color].code)
				if(!acolor) this.makeLayerSelect()
				this.printFile.layer = tempBestCombination['layer']['id']
				if(!alayer) this.makeFillingSelect()
				this.printFile.filling = tempBestCombination['fillings']['id']
				//this.priceFromDisplay = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format((tempPrice/this.printFile.quantity)/100)
				this.priceFrom = (tempPrice/this.printFile.quantity)/100
				this.priceTaxEcl = tempBestCombination['prices']['priceTaxEx']
				this.priceTaxInc = tempBestCombination['prices']['priceTaxIn']

				if(	this.printFile.technology != null &&
					this.printFile.material != null &&
					this.printFile.color != null &&
					this.printFile.layer != null &&
					this.printFile.filling != null
				){

					//console.log('Product ready to save')
				}

		    },
		    findMakers(combinations, product){
		    	//Iteration TECHNOLOGY
		    	let makerListToStore = []

		    	this.printFile.makersList = []

		    	for (let [k, v] of Object.entries(combinations)) {

		    		if(product.technology != k){ continue }

		    		//Iteration MATERIAL
		    		for (let [a, b] of Object.entries(v.materials)) {

		    			if(product.material!= a){ continue }

		    			//Iteation COLOR
		    			for (let [c, d] of Object.entries(b.colors)) {

		    				if(product.color!= c){ continue }

		    				//Iteration LAYER
		    				for (let [e, f] of Object.entries(d.layers)) {

		    					if(product.layer!= e){ continue }

		    					//Iteration FILLINGS
		    					for (let [g, h] of Object.entries(f.fillings)) {

		    						if(product.filling!= g){ continue }

		    						//Iteration PRICES
		    						for (let [i, j] of Object.entries(h.prices)) {

		    							//console.log('J',j)
		    							//console.log('I',i)
		    							let optionsTab = []

///////// COMPLETER POUR FINISHING ///////////////////////////////////////

										if(Object.keys(this.printFile.finishing).length > 0){

											//console.log('FINISHING PROCESS',j)
											//console.log('Options', this.printFile.finishing)

											let optionsToFind = Object.keys(this.printFile.finishing).length
											let optionsFound = 0

											if(Object.keys(j.options).length > 0){

												for(let option in this.printFile.finishing){

													/*console.log('PRODDDDDDDDD',option)*/
													
													for(let [l, m] of Object.entries(j.options)){

														/*console.log('TESSSSSS', this.printFile.finishing[option])
														console.log('TESSSSSS PROD',this.printFile.finishing[option].id)*/

														if(l == this.printFile.finishing[option].id){

															//optionsTab.push(this.printFile.finishing[option])
															optionsTab.push({ 'title' : this.refFinishing[l].name , 'id' : l, 'priceht' :  m.option_tax_excl , 'pricettc' : m.option_tax_incl})
															//console.log('OOOOPPPPPTTTTITONNNNNN',m)
															optionsFound ++

														}

													}

												}

												if(optionsToFind != optionsFound){

													continue

												} else {

													//console.log('MAKER WITH OPTION FOUND !!!!')

												}


											} else {

												continue

											}

										}

		    							this.printFile.makersList.push(
		    								{
		    									'name': this.makersDisplay[i].name,
		    									'id': i,
		    									'price_tax_incl' : j.price_tax_incl,
		    									'price_tax_excl' : j.price_tax_excl,
		    									'price_setup' : j.price_setup,
		    									'finishing' : optionsTab
		    								}
		    							)

		    							makerListToStore.push({
		    								'name': this.makersDisplay[i].name,
		    								'id': i,
		    								'productions': this.makersDisplay[i].productions,
		    								'bio': this.makersDisplay[i].bio,
		    								'pictures': this.makersDisplay[i].pictures,
		    								'rating' : this.makersDisplay[i].rating,
		    								'comments' : this.makersDisplay[i].comments,
		    								'pickup' : this.makersDisplay[i].pickup,
		    								'price_tax_incl' : j.price_tax_incl,
		    								'price_tax_excl' : j.price_tax_excl,
		    								'price_setup' : j.price_setup,
		    								'finishing' : optionsTab,
		    								'pickup_address' : this.makersDisplay[i].pickup.address,
		    							})
		    							
		    						}
		    					}
		    				}
		    			}
		    		}
				}



				store.commit('MAKE_MAKER_LIST',makerListToStore)
				//console.log('Makers list : ', makerListToStore)
		    },
		    apiCombination(x,y,z,volume,quantity,maker){


		    	console.log('API Combination')

		    	if(this.printFile.quantity == 0){

		    		this.printFile.quantity = 1
		    	}

		    	let makerTab = []

		    	if(this.makersList.length >0){
		    		for (const key in this.makersList){
		    			makerTab.push(this.makersList[key].id)
		    		}
		    	}

		    	var data = {"dimensions":{"x":this.printFile.dimensions.x,"y":this.printFile.dimensions.y,"z":this.printFile.dimensions.z},"volume":this.printFile.volume,"quantity":this.printFile.quantity,"makers": makerTab };

		    	this.$http.post(this.apiPricing, data ).then((response) => 
				{
					console.log('API Combination => success')

						var data = JSON.parse(response.body)

						/*this.combinations = data.combinations
						this.makersDisplay = data.makers
						this.refTechnology = data.ref.technology
						this.refMaterial = data.ref.material
						this.refColor = data.ref.color
						this.refLayer = data.ref.layer
						this.refFinishing = data.ref.finishing
						this.refFilling = data.ref.filling*/

						//if(Object.keys(this.combinations.technologies).length >= 1){
						if(Object.keys(data.combinations.technologies).length >= 1){

							this.combinations = data.combinations
							this.makersDisplay = data.makers
							this.refTechnology = data.ref.technology
							this.refMaterial = data.ref.material
							this.refColor = data.ref.color
							this.refLayer = data.ref.layer
							this.refFinishing = data.ref.finishing
							this.refFilling = data.ref.filling

							//console.log('OOOOPPPPTIOONNNN', this.refFinishing)
							this.makeTechnologiesSelect()
							this.error = null
							this.errorSize = false

						} else {

							this.errorSize = true
							this.error = 'no-maker'
							//console.log('No maker available')

						}

				}, (response) => {

					console.log('API Combination => error',response)

				})
		    },
		    makeTechnologiesSelect(){

		    	this.optionsTechnology = []
		    	this.printFile.finishing = []

				for (let [k, v] of Object.entries(this.combinations.technologies)) {

				    this.optionsTechnology.push({'text' : this.refTechnology[k].name,'value': k})

				}

				this.optionsTechnology.sort(function (a, b) {
					if(a.text < b.text) return -1
					if(a.text > b.text) return 1
					return 0
				});

				this.bestPriceCombination(this.combinations.technologies,false,false,false,false,false)
		    },
		    makeMaterialSelect(){

		    	this.optionsMaterial = []
		    	this.printFile.finishing = []

				for (let [k, v] of Object.entries(this.combinations.technologies[this.printFile.technology].materials)) {

				    this.optionsMaterial.push({'text' : this.refMaterial[k].name,'value': k})

				}

				this.optionsMaterial.sort(function (a, b) {
					if(a.text < b.text) return -1
					if(a.text > b.text) return 1
					return 0
				});

				 this.bestPriceCombination(this.combinations.technologies,this.printFile.technology,false,false,false,false)
		    },
		    makeColorSelect(){

				this.optionsColor = []
				this.printFile.finishing = []

				for (let [k, v] of Object.entries(this.combinations.technologies[this.printFile.technology].materials[this.printFile.material].colors)) {

				    //this.optionsColor.push({'text' : this.refColor[k].name +' - '+ this.refColor[k].code ,'value': k})
				    this.optionsColor.push({'text' : this.refColor[k].name,'value': k})
 
				}

				this.optionsColor.sort(function (a, b) {
					if(a.text < b.text) return -1
					if(a.text > b.text) return 1
					return 0
				});

				this.bestPriceCombination(this.combinations.technologies,this.printFile.technology,this.printFile.material,false,false,false)


		    },
		    makeLayerSelect(){

		    	this.changeColor(this.refColor[this.printFile.color].code)

		    	this.optionsLayer = []
		    	this.printFile.finishing = []
				for (let [k, v] of Object.entries(this.combinations.technologies[this.printFile.technology].materials[this.printFile.material].colors[this.printFile.color].layers)) {

				    this.optionsLayer.push({'text' : this.refLayer[k].name,'value': k})

				}

				//("123 hello everybody 4").replace(/(^\d+)(.+$)/i,'$1')
				this.optionsLayer.sort(function (a, b) {
					if((a.text).replace(/(^\d+)(.+$)/i,'$1') < (b.text).replace(/(^\d+)(.+$)/i,'$1')) return -1
					if((a.text).replace(/(^\d+)(.+$)/i,'$1') > (b.text).replace(/(^\d+)(.+$)/i,'$1')) return 1
					return 0
				});

				this.bestPriceCombination(this.combinations.technologies,this.printFile.technology,this.printFile.material,this.printFile.color,false,false)
		    },
		    makeFillingSelect(){

		    	//console.log('Layer selected =>', this.printFile.layer)

		    	this.optionsFilling = []
		    	this.printFile.finishing = []
				for (let [k, v] of Object.entries(this.combinations.technologies[this.printFile.technology].materials[this.printFile.material].colors[this.printFile.color].layers[this.printFile.layer].fillings)) {

				    this.optionsFilling.push({'text' : this.refFilling[k].name,'value': k})

				}

				this.bestPriceCombination(this.combinations.technologies,this.printFile.technology,this.printFile.material,this.printFile.color,this.printFile.layer,false)
		    },
		    makeFinishingCheckbox(){

		    	//console.log('Filling selected =>', this.printFile.filling)
		    	this.printFile.finishing = []
		    	this.bestPriceCombination(this.combinations.technologies,this.printFile.technology,this.printFile.material,this.printFile.color,this.printFile.layer,this.printFile.filling)

		    	// Function for Finishin Options
		    	/*this.checkboxFinishing = []
				for (let [k, v] of Object.entries(this.combinations.technologies[this.printFile.technology].materials[this.printFile.material].colors[this.printFile.color].layers[this.printFile.layer].fillings[this.printFile.filling].prices)) {

					console.log(this.makersDisplay[k].name);

				    this.checkboxFinishing.push({'text' : this.makersDisplay[k].name,'value': k})
				}*/

		    },
		    /*handleFinishing(){
		    	if(this.printFile.finishing.length > 0){

		    		console.log('handleFinishing =>')
		    		this.bestPriceCombination(this.combinations.technologies,this.printFile.technology,this.printFile.material,this.printFile.color,this.printFile.layer,this.printFile.filling)

		    	}
		    },*/
		    validateProduct(){

		    	this.printFile.state = 'lock'

				// Google Tag Manager : push event file validated
				//******************************************** */
				gtag_report_event(this.user3dm,'impression_form','impression_form.file_validated')
				//******************************************** */

				var gtagInfoFile = {
					"technologyLabel": this.printFile.technologyLabel,
					"technology":this.printFile.technology,
					"materialLabel":this.printFile.materialLabel,
					"material":this.printFile.material,
					"color":this.printFile.color,
					"layer":this.printFile.layer,
					"filling":this.printFile.filling,
					"finishing":this.printFile.finishing,
					"dimension":this.printFile.dimensions,
					"volumeDisplay":this.printFile.volumeDisplay,
					"fileName":this.printFile.filename,
					"extension":this.printFile.extension,
					"quantity":this.printFile.quantity,
					"firstPriceHT":this.priceFrom

				}

				gtag_report_event(this.user3dm,'impression_form','impression_search',gtagInfoFile)


		    	this.findMakers(this.combinations.technologies, this.printFile)
		    	this.updateStoreTechnology()
		    	this.updateStoreMaterial()
		    	this.updateStoreColor()
		    	this.updateStoreLayer()
		    	this.updateStoreFilling()
		    	this.updateStoreDimension()
		    	this.updateStoreFileName()
		    	this.updateStoreQuantity()
		    	this.updateStoreState()
		    	this.updatePriceMaker()
		    	this.updateStoreFileData()

		    },
		    editProduct(){

		    	this.printFile.state = 'open'
		    	this.updateStoreState()
	  			store.commit('CHANGE_STEP',1)
	  			if(this.productId == 0){
	  				store.commit('RESET_MAKER')
	  			}


		    },
		    deleteProduct(){
		    	//console.log('Delete product => ID : ', this.productId)

		    	store.commit('DELETE_PRODUCT',{ 'productId' : this.productId})
		    	store.commit('CHANGE_STEP',1)

		    	//store.dispatch('deleteProduct',{ 'productId' : this.productId})
		    },
		     updateStoreQuantity() {
			    store.commit('UPDATE_3DFILE_QUANTITY', { 'productId' : this.productId, 'quantity' : this.printFile.quantity})
			},
		    updateStoreTechnology() {
			    store.commit('UPDATE_3DFILE_TECHNOLOGY', { 'productId' : this.productId, 'technology' : this.printFile.technology, 'label' : this.printFile.technologyLabel})
			},
			updateStoreMaterial() {
			    store.commit('UPDATE_3DFILE_MATERIAL', { 'productId' : this.productId, 'material' : this.printFile.material, 'label' : this.printFile.materialLabel})
			},
			updateStoreColor() {
			    store.commit('UPDATE_3DFILE_COLOR', { 'productId' : this.productId, 'color' : this.printFile.color})
			},
		    updateStoreLayer() {
			    store.commit('UPDATE_3DFILE_LAYER', { 'productId' : this.productId, 'layer' : this.printFile.layer})
			},
			updateStoreFilling() {
			    store.commit('UPDATE_3DFILE_FILLING', { 'productId' : this.productId, 'filling' : this.printFile.filling})
			},
			updateStoreDimension() {
			    store.commit('UPDATE_3DFILE_DIMENSION', { 'productId' : this.productId, 'data' : {
			    	'volume' : this.printFile.volume,
			    	'x' : this.printFile.dimensions.x,
			    	'y' : this.printFile.dimensions.y,
			    	'z' : this.printFile.dimensions.z,
			    }})
			},
			updateStoreFileName() {
			    store.commit('UPDATE_3DFILE_FILE_NAME', { 'productId' : this.productId, 'filename' : this.printFile.filename})
			},
			updateStoreState() {
			    store.commit('UPDATE_3DFILE_STATE', { 'productId' : this.productId, 'state' : this.printFile.state})
			},
			updatePriceMaker() {
				store.commit('UPDATE_3DFILE_PRICE_MAKER', {'productId' : this.productId, 'makers' : this.printFile.makersList})
			},
			updateStoreFileData(){
				store.commit('UPDATE_3DFILE_FILE', {'productId' : this.productId, 'file' : this.printFile.file})
			},
			updateTmpCart(){
				store.commit('UPDATE_TMP_CART', {'productId': this.productId, 'price' : this.tmpPrice, 'priceFinition' : this.tmpPriceFinition })
			},
		},
		watch: {
	        quantityComputed: function(){
	        	this.printFile.finishing = []
	        },
	        finishingComputed: function(){

	        	if(this.stateFinishing != this.printFile.finishing.length){

						//console.log('FINISHING CHANGE !!!!!')
			    		this.bestPriceCombination(this.combinations.technologies,this.printFile.technology,this.printFile.material,this.printFile.color,this.printFile.layer,this.printFile.filling)

			    		this.stateFinishing = this.printFile.finishing.length
			    }

	        },
	        selectedUnity: function(val){
				this.makersDisplay = {}
	        	//console.log('Change unity',val)
	        	this.recalculateDimension('unity')


	        },
	        priceFrom: function(val){

	        	this.updateTmpCart()
	        	//console.log('tmpPrice', this.tmpPrice)
	        	//console.log('tmpPriceFinition', this.tmpPriceFinition)
	        	//console.log('tmpVat', (this.tmpPrice+this.tmpPriceFinition)*1.2)
	        },
		},
		filters: {
		  lineBreak: function (value) {

		  	return value.replace(/(?:\r\n|\r|\n)/g, '<br />');
		    
		  }
		}
	}
</script>

<style>

</style>