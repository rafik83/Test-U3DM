<template>
	<div>   
            <div class="col-sm-12" v-if="stateProject != 'open' && projectOrigin.status < 4">
            	<div class="col-sm-3"><label for="">{{label1_1}}<i>*</i> :</label></div>
				
            	<div class="col-sm-9" v-if="stateProject == 'open'">
            		<input type="text" class="col-sm-12" @input="interactiveAction" v-model="project.name" placeholder="Saisissez un nom pour votre projet" v-on:input="checkInput('name')" v-bind:class="{ errorField : fieldError['name'] }">
            	</div>
            	<div class="col-sm-3" v-else>
            		<p>{{project.name}}</p>
            	</div>
				<div class="col-sm-6 text-right" v-if="stateProject != 'open' && projectOrigin.status < 4">
					Devis attendus au plus tard <br>
					{{ expiredDate }} à 13h00					
				</div>
            </div>
            <div class="col-sm-12" v-else>
            	<div class="col-sm-3"><label for="">{{label1_1}}<i>*</i> :</label></div>
            	<div class="col-sm-9" v-if="stateProject == 'open'">
            		<input type="text" class="col-sm-12" @input="interactiveAction" v-model="project.name" placeholder="Saisissez un nom pour votre projet" v-on:input="checkInput('name')" v-bind:class="{ errorField : fieldError['name'] }">
            	</div>
            	<div class="col-sm-3" v-else>
            		<p>{{project.name}}  </p>
            	</div>
				<div class="col-sm-6 text-right" v-if="stateProject != 'open' && projectOrigin.status < 4">
					Devis attendus au plus tard <br>
					{{ expiredDate }} à 13h00					
				</div>
            </div>

            <div class="col-sm-12">
            	<div class="col-sm-3"><label for="">TYPE DE PROJET<i>*</i> :</label></div>
            	<div class="col-sm-9" v-if="stateProject == 'open'">
            		<select name="projectType" id="" @input="interactiveAction" v-model="projectType">
            			<option value="0" disabled>Sélectionner votre type de projet</option>
            			<option :value="project" v-for="project in refProjectType">{{project.name}}</option>
            		</select>
            		<p v-html="referentialType" v-if="referentialType" style="padding-left:10px;padding-right:10px;">
            		</p>
            	</div>
            	<div class="col-sm-9" v-else>
            		<p>{{projectType.name}}</p>
            	</div>
            </div>
            <div class="col-sm-12">
            	<div class="col-sm-3"><label for="">DATE RENDU SOUHAITÉE<i>*</i> :</label></div>
            	<div class="col-sm-9" v-if="stateProject == 'open'">
            		<div class="btn btn-sm btn-form-off" id="delivery-time-1" @click="toogleRef('deliveryTime','one_week',1)">1 semaine</div>
            		<div class="btn btn-sm btn-form-off" id="delivery-time-2" @click="toogleRef('deliveryTime','fifteen_days',2)">15 jours</div>
            		<div class="btn btn-sm btn-form-off" id="delivery-time-3" @click="toogleRef('deliveryTime','one_month',3)">1 mois</div>
            		<div class="btn btn-sm btn-form-off" id="delivery-time-4" @click="toogleRef('deliveryTime','three_months',4)">3 mois</div>
            		<div class="btn btn-sm btn-form-off" id="delivery-time-5" @click="toogleRef('deliveryTime','more_than_three_months',5)">>3 mois</div>
            	</div>
            	<div class="col-sm-9" v-else>
            		<div class="btn btn-sm">{{ deliveryTimeWording }}</div>
            	</div>
            </div>
            <div class="col-sm-12">
            	<div class="col-sm-3"><label for="">DESCRIPTION<i>*</i> :</label></div>
            	<div class="col-sm-9" v-if="stateProject == 'open'">
            		<textarea name="" id="" cols="30" rows="10"  @input="interactiveAction" v-model="project.description" v-on:input="checkInput('description')" v-bind:class="{ errorField : fieldError['description'] }">
            		</textarea>
            	</div>
            	<div class="col-sm-9" v-else>
            		<p> <textarea cols="30" rows="10" readonly disabled > {{project.description}}</textarea></p>
            	</div>
            </div>
            <div class="col-sm-12" style="margin-top: 15px;">
            	<div class="col-sm-3"><label for="">APPLICATIONS<i>*</i> :</label></div>
            	<div class="col-sm-9" v-if="stateProject == 'open'">

            		<span v-for="field in refFields" class="infosbull positionForInfosbull">
	            		<div class="btn btn-sm btn-form-off" @click="toogleRef('fields',field)" :id="'fields-'+field.id">{{ field.name }}</div>
	            		<div v-html="field.description" v-show="field.description != null"></div>
            		</span>
            	</div>
            	<div class="col-sm-9" v-else>
	            		<div class="btn btn-sm " v-for="field in project.fields">{{ field }}</div>
            	</div>
            </div>
            <div class="col-sm-12" v-if="stateProject == 'open'" style="margin-top: 15px;">
            	<div class="col-sm-3"><label for="">FICHIER :</label></div>
            	<div class="col-sm-9">
            		<div v-for="(file, index) in files" class="col-sm-12" style="padding-left:0px;" :id="'file-div-'+index">
            			<div class="">
            				<input type="file" class="col-sm-7 btn btn-sm btn-form-off" :id="'file-'+index" @change="processFile($event,index)" :key="index">
            			</div>
            			<div class="col-sm-2">
            				<div class="btn btn-sm" @click="deleteFile(index)">-</div>
            			</div>
            		</div>
            		<div class="btn btn-sm" @click="addFile()">+ Ajouter un fichier</div>
            	</div>
            </div>
            <div class="col-sm-12" v-if="projectOrigin != null & viewSavefield">
            	<div class="col-sm-3"><label for="">FICHIER(S) ENREGISTRÉ(S) :</label></div>
            	<div class="col-sm-9">
            		<div v-for="(file, index) in originFiles" class="col-sm-12" style="padding-left:0px;">
            			<div class="col-sm-7">
            				<a v-bind:href="file.url_download">{{ file.original_name }}</a>
            			</div>
            			<div class="col-sm-3" v-if="stateProject == 'open'">
            				<div class="btn btn-sm" @click="deleteRealFile(index,file.id)"><i class="far fa-trash-alt fa-1x" style="color:#FFFFFF !important;"></i></div>
            			</div>
            		</div>
            	</div>
            </div>
            <div >
	            <div class="col-sm-12" style="padding-top:20px;" v-if="requiredAddressBlock" >
	            	<div class="col-sm-3"><label for="">{{this.addressLabel}}<i>*</i> :</label></div>
	            	<div class="col-sm-9" v-if="stateProject == 'open'">
	            		<div class="col-sm-12" style="padding-left:0px !important;">
	            			<input type="text" v-model="project.address.lastname" placeholder="Nom*" maxlength="255" class="col-sm-4" v-on:input="checkInput('lastname')" v-bind:class="{ errorField : fieldError['lastname'] }">
	            			<input type="text" v-model="project.address.firstname" placeholder="Prénom*" maxlength="255" class="col-sm-4" v-on:input="checkInput('firstname')" v-bind:class="{ errorField : fieldError['firstname'] }">
	            		</div>
	            		<div class="col-sm-12" style="padding-left:0px !important;">
	            			<input type="text" v-model="project.address.street1" placeholder="Adresse*" maxlength="255" class="col-sm-4" v-on:input="checkInput('street1')" v-bind:class="{ errorField : fieldError['street1'] }">
	            			<input type="text" v-model="project.address.street2" placeholder="Adresse complémentaire" maxlength="255" class="col-sm-4">
	            		</div>
	            		<div class="col-sm-12" style="padding-left:0px !important;">
	            			<input type="text" v-model="project.address.zipcode" placeholder="Code postal*" maxlength="7" v-on:input="checkInput('zipcode')" v-bind:class="{ errorField : fieldError['zipcode'] }">
		            		<input type="text" v-model="project.address.city" placeholder="Ville*" maxlength="255" v-on:input="checkInput('city')" v-bind:class="{ errorField : fieldError['city'] }">
		            		<input type="text" v-model="project.address.telephone" placeholder="Téléphone*" maxlength="13" v-on:input="checkInput('telephone')" v-bind:class="{ errorField : fieldError['telephone'] }">
	            		</div>
	            	</div>
	            	<div class="col-sm-9" v-else>
	            		<div class="col-sm-12" style="padding-left:0px !important;">
	            			<p>{{project.address.lastname}} {{project.address.firstname}} ({{ project.address.telephone}}) <br>
	            			{{project.address.street1}} {{project.address.street2}} - {{ project.address.zipcode }} {{ project.address.city }}</p>
	            		</div>
	            	</div>
	            </div>
	            <div  class="col-sm-12" v-if="requiredScanBlock">
	            	<div class="col-sm-3"><label for="">DIMENSIONS OBJET À SCANNER<i>*</i> : <br>en millimètre</label></div>
	            	<div class="col-sm-9" v-if="stateProject == 'open'">
	            		<label for=""><input class="" type="number" min="0" placeholder="X" v-model="project.dim.x" v-on:input="checkInput('dimX')" v-bind:class="{ errorField : fieldError['dimX'] }">mm</label>
	            		<label for=""><input class="" type="number" min="0" placeholder="Y" v-model="project.dim.y" v-on:input="checkInput('dimY')" v-bind:class="{ errorField : fieldError['dimY'] }">mm</label>
	            		<label for=""><input class="" type="number" min="0" placeholder="Z" v-model="project.dim.z" v-on:input="checkInput('dimZ')" v-bind:class="{ errorField : fieldError['dimZ'] }">mm</label>
	            	</div>
	            	<div class="col-sm-9" v-else>
	            		<p>{{project.dim.x}} X {{project.dim.y}} X {{project.dim.z}} mm</p>
	            	</div>
	            </div>
	            <!--<div class="col-sm-12">
	            	<div class="col-sm-3"></div>
	            	<div class="col-sm-9" v-if="stateProject == 'open'">
	            		<div class="checkbox checkbox-inline">
	            			<label for="maker-come">
	            				<input type="checkbox" id="maker-come" name="maker-come" v-model="project.scanOnSite" value="1"/>
	            				<span></span>
	            				<span class="wrapped-label">Je souhaite que le maker se déplace</span>
	            			</label>
	            		</div>
	            	</div>
	            	<div class="col-sm-9" v-else>
	            		<p v-if="project.scanOnSite"><i class="far fa-check-square"></i> Je souhaite que le maker se déplace</p>
	            	</div>
	            </div>-->
			</div>
			<!-- <div class="col-sm-12" style="padding-top:20px;" v-if="stateProject == 'open'">
            	<div class="col-sm-3"><label>OPTIONNEL :</label></div>
            </div> -->
            <div class="col-sm-12" style="margin-top: 15px;">
            	<div class="col-sm-3" v-if="stateProject == 'open' || project.skills.length > 0"><label for="">COMPÉTENCES :</label></div>
            	<div class="col-sm-9" v-if="stateProject == 'open'">
            		<span v-for="skill in refSkills" class="infosbull positionForInfosbull">
	            		<div class="btn btn-sm btn-form-off" @click="toogleRef('skills',skill)" :id="'skills-'+skill.id">
	            			{{ skill.name }}
	            		</div>
	            		<div v-html="skill.description" v-show="skill.description != null"></div>
            		</span>
            	</div>
            	<div class="col-sm-9" v-else>
            		<div class="btn btn-sm" v-for="skill in project.skills">{{ skill }}</div>
            	</div>
            </div>
            <div class="col-sm-12" style="margin-top: 15px;">
            	<div class="col-sm-3" v-if="stateProject == 'open' || project.softwares.length > 0"><label for="">LOGICIELS :</label></div>
            	<div class="col-sm-9" v-if="stateProject == 'open'">
            		<span v-for="software in refSoftwares" class="infosbull positionForInfosbull">
	            		<div class="btn btn-sm btn-form-off" @click="toogleRef('softwares',software)" :id="'softwares-'+software.id">
	            			{{ software.name }}
	            		</div>
	            		<div v-html="software.description" v-show="software.description != null"></div>
            		</span>
            	</div>
            	<div class="col-sm-9" v-else>
            		<div class="btn btn-sm " v-for="software in project.softwares">{{ software }}</div>
            	</div>
            </div>
            <div class="col-sm-12" v-if="stateProject == 'open'">
            	<div class="col-sm-3">
            		<i>* champs obligatoires</i>
            	</div>
            </div>
    </div>
</template>


<script>
	
	import Vue from 'vue'
	import { mapGetters } from 'vuex'
	import store from '../stores/PrintStore'

	export default {
		name: "projectDesign",
		components: {},
		store: store,
		props: [
			'apiUrlReferentielDesign',
			'apiUrlDeleteFile',
			'stateProject',
			'tagSpecial'
		],
		data: function(){
			return {
				firstInteractiveAction: true,
				refProjectType: null,
				refSkills: [],
				refSoftwares: [],
				refFields: null,

				projectType: 0,

				stepProject: 1,
				requiredAddressBlock:0,
				requiredScanBlock:0,

				project: {
					'fields':[],
					'softwares':[],
					'skills':[],
					'description':'',
					'name': '',
					'dim': {
						'x': '',
						'y': '',
						'z': ''
					},
					'address': {
						'street1': '',
						'street2': '',
						'zipcode': '',
						'firstname': '',
						'lastname': '',
						'city': '',
						'country': 'FR',
						'telephone': ''
					},
					'scanOnSite':false,
					'projectType': 0,
					'deliveryTime': '',
					'file':[],
				},
				files:[],
				originFiles:[],
				viewSavefield: false,
				deliveryTime:'',
				deliveryTimeWording: '',
				fieldError: [],
				referentialType: null,
				expiredDate : expiredDate,
				label1_1 : '',

			}
		},
		beforeMounted (){

		},
		mounted (){

			this.label1_1 = 'NOM DU PROJET';
			if (tagSpecial == "COVID") {
				this.label1_1 = "REFERENCE ENTREPRISE";
				}
			self = this;

			// Project in creation
			if(this.stateProject == 'open') {
				// Reading existing project
				this.callRef()

			} else if(this.stateProject == 'close'){
				// Just displaying value in form 
				this.projectType = this.projectOrigin.type[0];

				this.projectOrigin.fields.forEach(function(element){
					self.project.fields.push(element.name);
				});

				this.projectOrigin.skills.forEach(function(element){
					self.project.skills.push(element.name);
				});

				this.projectOrigin.softwares.forEach(function(element){
					self.project.softwares.push(element.name);
				});

			}

			if(Object.keys(this.projectOrigin).length > 0 && this.projectOrigin.constructor === Object){

				this.projectType = this.projectOrigin.type[0];

				this.project.id = this.projectOrigin.id;
				this.project.name = this.projectOrigin.name;
				this.project.description = this.projectOrigin.description;
				this.project.dim.x = this.projectOrigin.dimensions.x;
				this.project.dim.y = this.projectOrigin.dimensions.y;
				this.project.dim.z = this.projectOrigin.dimensions.z;
				this.project.scanOnSite = this.projectOrigin.scan_on_site;
				this.project.deliveryTime = this.projectOrigin.delivery_time;
				this.deliveryTime = this.projectOrigin.delivery_time;
				this.project.reference = this.projectOrigin.reference;

				if(this.projectOrigin.scan_address != null){

					this.project.address.firstname = this.projectOrigin.scan_address.firstname;
					this.project.address.lastname = this.projectOrigin.scan_address.lastname;
					this.project.address.company = this.projectOrigin.scan_address.company;
					this.project.address.street1 = this.projectOrigin.scan_address.street1;
					this.project.address.street2 = this.projectOrigin.scan_address.street2;
					this.project.address.city = this.projectOrigin.scan_address.city;
					this.project.address.zipcode = this.projectOrigin.scan_address.zipcode;
					this.project.address.telephone = this.projectOrigin.scan_address.telephone;
				}

				if(this.projectOrigin.files.length > 0){

					this.viewSavefield = true;

					this.projectOrigin.files.forEach(function(element){
						self.originFiles.push(element);
					});

				}
			}

		},
		computed: {
			...mapGetters([
				'user3dm',
				'projectStore',
				'projectOrigin',
				'projectFiles',
			]),
		},
		methods: {
			interactiveAction: function(){
				if (this.firstInteractiveAction == true ) {
					// Google Tag Manager : push event account creation started
					//******************************************** */
					gtag_report_event(this.user3dm,'project_form','project_form.started')
					//******************************************** */
				}
				this.firstInteractiveAction=false
			},
			checkFormProject(){

				let errors = [];

				if(this.project.name.trim() == "")
					errors.push('Name')

				if(this.project.description.trim() == '')
					errors.push('Name')

				if(this.project.projectType == '')
					errors.push('Name')

				if(this.project.fields.length <= 0)
					errors.push('Name')

				if(this.project.deliveryTime == '')
					errors.push('Name')

				if(this.projectType.scanner){

					if(this.project.address.street1.trim() == '')
						errors.push('Name')

					if(this.project.address.zipcode == '' || RegExp(/^((0[1-9])|([1-8][0-9])|(9[0-8])|(2A)|(2B))[0-9]{3}$/).test(this.project.address.zipcode) == false){
						errors.push('Name')
					}

					if(this.project.address.city.trim() == '')
						errors.push('Name')

					if(this.project.address.telephone == '' || RegExp(/^\+?\s*(\d+\s?){8,}$/).test(this.project.address.telephone) == false)
						errors.push('Name')

					if(this.project.address.lastname.trim() == '')
						errors.push('Name')	

					if(this.project.address.firstname.trim() == '')
						errors.push('Name')	

					if(this.project.dim.x.trim() == '' || this.project.dim.x <= 0)
						errors.push('Name')

					if(this.project.dim.y.trim() == '' || this.project.dim.y <= 0)
						errors.push('Name')

					if(this.project.dim.z.trim() == '' || this.project.dim.z <= 0)
						errors.push('Name')	

				}

				if(errors.length == 0){

					if (Object.keys(this.user3dm).length === 0 && this.user3dm.constructor === Object){

						store.commit('CHANGE_STEP_PROJECT',2)

					} else {

						store.commit('CHANGE_STEP_PROJECT',3)

					}

				} else {

					store.commit('CHANGE_STEP_PROJECT',1)
				}

				

				store.commit('UPDATE_PROJECT',this.project)

			},
			callRef(){

				this.$http.post(this.apiUrlReferentielDesign).then((response) => 
				{
					//console.log('API Ref => success',response)

						var data = JSON.parse(response.body)

						if (tagSpecial == "COVID") {this.refProjectType = data.ref.projectTypeSpec}
						else {this.refProjectType = data.ref.projectType}
						this.refSkills = data.ref.skill
						this.refSoftwares = data.ref.software
						this.refFields = data.ref.field

						// Update display and project
						if(Object.keys(this.projectOrigin).length > 0 && this.projectOrigin.constructor === Object){

							self = this;

							setTimeout(function(){ 
								self.projectOrigin.fields.forEach(function(element){
									self.toogleRef('fields',element);
								});

								self.projectOrigin.softwares.forEach(function(element){
									self.toogleRef('softwares',element);
								});

								self.projectOrigin.skills.forEach(function(element){
									self.toogleRef('skills',element);
								});

								if(self.project.deliveryTime == 'one_week'){

									self.toogleRef('deliveryTime',self.project.deliveryTime,1);

								} else if(self.project.deliveryTime == 'fifteen_days'){

									self.toogleRef('deliveryTime',self.project.deliveryTime,2);

								} else if(self.project.deliveryTime == 'one_month'){

									self.toogleRef('deliveryTime',self.project.deliveryTime,3);

								} else if(self.project.deliveryTime == 'three_months'){

									self.toogleRef('deliveryTime',self.project.deliveryTime,4);

								} else if(self.project.deliveryTime == 'more_than_three_months'){

									self.toogleRef('deliveryTime',self.project.deliveryTime,5);

								}

							}, 500);

							this.projectType = this.projectOrigin.type[0];

						}

				}, (response) => {

					console.log('API Ref => error',response)

				})

			},
			checkInput(field){

				if(field == 'description'){
					if(this.project.description.trim() == ''){

						this.fieldError['description'] = true;

					} else {

						this.fieldError['description'] = false;
					}
				}

				if(field == 'name'){
					if(this.project.name.trim() == ''){

						this.fieldError['name'] = true;

					} else {

						this.fieldError['name'] = false;
					}
				}

				if(field == 'lastname'){
					if(this.project.address.lastname.trim() == ''){

						this.fieldError['lastname'] = true;

					} else {

						this.fieldError['lastname'] = false;
					}
				}

				if(field == 'firstname'){
					if(this.project.address.firstname.trim() == ''){

						this.fieldError['firstname'] = true;

					} else {

						this.fieldError['firstname'] = false;
					}
				}

				if(field == 'city'){
					if(this.project.address.city.trim() == ''){

						this.fieldError['city'] = true;

					} else {

						this.fieldError['city'] = false;
					}
				}

				if(field == 'street1'){
					if(this.project.address.street1.trim() == ''){

						this.fieldError['street1'] = true;

					} else {

						this.fieldError['street1'] = false;
					}
				}

				if(field == 'zipcode'){
					if(this.project.address.zipcode.trim() == '' || RegExp(/^((0[1-9])|([1-8][0-9])|(9[0-8])|(2A)|(2B))[0-9]{3}$/).test(this.project.address.zipcode) == false){

						this.fieldError['zipcode'] = true;

					} else {

						this.fieldError['zipcode'] = false;
					}
				}

				if(field == 'telephone'){
					if(this.project.address.telephone.trim() == '' || RegExp(/^\+?\s*(\d+\s?){8,}$/).test(this.project.address.telephone) == false){

						this.fieldError['telephone'] = true;

					} else {

						this.fieldError['telephone'] = false;
					}
				}

				if(field == 'dimX'){

					if(this.project.dim.x.trim() == '' || this.project.dim.x <= 0){

						this.fieldError['dimX'] = true;

					} else {

						this.fieldError['dimX'] = false;
					}

				}

				if(field == 'dimY'){

					if(this.project.dim.y.trim() == '' || this.project.dim.y <= 0){

						this.fieldError['dimY'] = true;

					} else {

						this.fieldError['dimY'] = false;
					}

				}

				if(field == 'dimZ'){

					if(this.project.dim.z.trim() == '' || this.project.dim.z <= 0){

						this.fieldError['dimZ'] = true;

					} else {

						this.fieldError['dimZ'] = false;
					}

				}


			},
			toogleRef(ref,obj,index = null){
				//console.log('toogleRef()=>',obj)
				self.interactiveAction ()

				// Toogle for Project -> field + software + skills
				if(ref == 'fields' || ref == 'softwares' || ref == 'skills'){

					if(this.project[ref].indexOf(obj.id) > -1){

						this.project[ref].splice(this.project[ref].indexOf(obj.id), 1);
						$( "#"+ref+"-"+obj.id ).addClass( "btn-form-off" );

					} else {

						this.project[ref].push(obj.id);
						$( "#"+ref+"-"+obj.id ).removeClass("btn-form-off");

					}
				}
				// Toogle for DeliveryTime
				if(ref == 'deliveryTime'){
					//console.log('index',index);
					this.project.deliveryTime = obj;
					for (var i = 1; i < 6; i++) {
						if(index == i){
							$( "#delivery-time-"+i).removeClass("btn-form-off");
						} else {
							$( "#delivery-time-"+i).addClass("btn-form-off");
						}
					}

				}
			},
			processFile(event,index) {
				
				this.project.file[index] = event.target.files[0]
			},
			addFile(){
				this.interactiveAction()
				if(this.files.length == 0){
					this.files.push('0');
				} else {
					this.files.push('0');
				}

			},
			deleteFile(index){

				$('#file-'+index).val('');
				$('#file-div-'+index).remove();
				this.project.file.splice(index,1);

				//this.files.splice(index, 1);
				
			},
			deleteRealFile(index,idFile){

				let url = this.apiUrlDeleteFile;
				url = url.replace(/xx/i, idFile);
				url = url.replace(/yy/i, this.projectOrigin.id);

				this.$http.post(url).then((response) => 
				{

					this.originFiles.splice(index,1);

				}, (response) => {

					console.log('API Delete file => error',response)

				})

			},
		},
		watch: {
			projectType: function(val){
				this.project.deliveryTime='one_week'

				if(val != 0 && val.scanner == 1){			
					this.requiredScanBlock = 1;
				} else {
					this.requiredScanBlock = 0;
				}
				if(val != 0 && val.addressProject == 1){			
					this.requiredAddressBlock = 1;
					//Init Address whith address user
					if (val.addressProjectLabel == null ) {this.addressLabel="ADRESSE";} 
						else { this.addressLabel=val.addressProjectLabel.toUpperCase();}
						
					if(this.project.address.lastname == '' && this.user3dm.address_shipping){
						let userAddress = this.user3dm.address_shipping;
						this.project.address.lastname = userAddress.lastname;
						this.project.address.firstname = userAddress.firstname;
						this.project.address.street1 = userAddress.street1;
						this.project.address.street2 = userAddress.street2;
						this.project.address.zipcode = userAddress.zipcode;
						this.project.address.city = userAddress.city;
						this.project.address.company = userAddress.company;
						this.project.address.telephone = userAddress.phone;
					}
				} else {
					this.requiredAddressBlock = 0;
				}

				this.project.projectType = val.id

				if(this.projectType.description){
					this.referentialType = this.projectType.description;
				} else {
					this.referentialType = null;
				}

			},
			project: {
		    	handler(obj){
		    		this.checkFormProject();
		    		//console.log('Checkform');
		     	},
		    deep: true
		  	},
		  	projectFiles: function(val){
		  		this.originFiles = val;
		  		this.files = [];
		  	},
		  	deliveryTime: function(val){
		  		if(val == 'one_week'){
					
					if (tagSpecial == "COVID") {this.deliveryTimeWording = "Flux Tendu"}
					else {this.deliveryTimeWording = "1 semaine"}

				} else if(val == 'fifteen_days'){

					this.deliveryTimeWording = "2 semaines"

				} else if(val == 'one_month'){

					this.deliveryTimeWording = "1 mois"

				} else if(val == 'three_months'){

					this.deliveryTimeWording = "3 mois"

				} else if(val == 'more_than_three_months'){

					this.deliveryTimeWording = "+ de 3 mois"

				}
		  	},
		  	projectStore: {
		    	handler(obj){
		    		if(obj.id > 0 && (Object.keys(this.projectOrigin).length === 0 && this.projectOrigin.constructor === Object)){

		    			console.log('ID =>',obj.id)

		    		}
		     	},
		    deep: true
		  	},
		}
	}
</script>

<style>

</style>