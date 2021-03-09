<template>
	<div>
		<div id="project" class="col-sm-12 bg-white pad-15 mrg-t-40">
           <div class="row mrg-20-0" v-if="state == 'open'">
		    	<div class="col-sm-6 text-center">
		    		<!-- <button class="btn btn-default btn-rounded mrg-0 btn-form-off" v-on:click="sendProject(1)" data-toggle="modal" data-target="#saved-modal"> -->
		    		<button class="btn btn-default btn-rounded mrg-0 btn-form-off" v-on:click="sendProject(1)">
		    		<!-- <button class="btn btn-default btn-rounded mrg-0 btn-form-off" v-on:click="sendProject(1)"> -->
		            	Sauvegarder mon projet
		            </button>
		    	</div>
		    	<div class="col-sm-6 text-center">
		    		<button class="btn btn-default btn-rounded mrg-0" v-on:click="sendProject(2)">
		            	Envoyer mon projet
		            </button>
		    	</div>
            </div>
            <div class="row mrg-20-0"  v-else>
            	<div class="col-sm-12 text-center">
					<p>United 3d Makers vous remercie de la création de votre projet.</p>

					<p>L'équipe United 3D Makers.</p>

						<button v-on:click="refreshPage()" class="btn btn-default btn-rounded" >Créer un nouveau projet</button>
				</div>
            </div>
    	</div>
    	<!-- Modal  Rating -->
		<div class="modal fade" ref="saveModal" id="saved-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content" style="background-color:#FFFFFF;">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title" id="myModalLabel"></h4>
		      </div>
		      <div class="modal-body">
		        <div>
		        	<p style="margin-bottom:0px;color:#000">Votre projet a bien été enregistré</p>
		        </div>
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
		      </div>
		    </div>
		  </div>
		</div>
		<!-- End Modal Rating -->
    </div>
</template>


<script>
	
	import Vue from 'vue'
	import { mapGetters } from 'vuex'
	import store from '../stores/PrintStore'

	export default {
		name: "saveProjectDesign",
		components: {},
		store: store,
		props: [
			'urlApiSendProject',
		],
		data: function(){
			return {
				state: 'open',
				projectId : null,
			}
		},
		mounted (){
			
		},
		computed: {
			...mapGetters([
				'user3dm',
				'projectStore',
				'stepFormProject',
				'urlNewProject'
			]),
		},
		methods: {
			sendProject(status){

				var formData = new FormData()

				console.log('Project ID',this.projectStore.id)
				console.log('this.projectId',this.projectId)
				let idProject = null ;

				if(this.projectStore.id == null){

					idProject = this.projectId;

				} else {

					idProject = this.projectStore.id;

				}
				

				formData.append('project', JSON.stringify(this.projectStore));
				formData.append('project_id',idProject);
				formData.append('status', status);
				formData.append('customer_id', this.user3dm.id);

				let nbFile = 0;
				//Files
				this.projectStore.file.forEach(function(element) {
				  formData.append('file-'+nbFile, element);
				  nbFile++;
				});

				this.$http.post(this.urlApiSendProject, formData ).then((response) => 
				{
					console.log('API Send Project => success',response)
					var data = JSON.parse(response.body);

					store.commit('SET_SAVE_TIMESTAMP');

					console.log('DATA response project', JSON.parse(data.files))

					if(status == 1){

						if(this.projectId == null && this.projectStore.id == null){

							//Update reference after saving
							store.commit('UPDATE_PROJECT_REFERENCE',data.project_reference);
							store.commit('UPDATE_PROJECT_ID',data.project_id);
							this.projectId = data.project_id;
							// Ajax process
							//window.history.pushState("", "", window.location.toString() + '/'+data.project_reference);
							// Refresh process
							window.location = window.location.toString() + '/'+data.project_reference;

						} else {

							//Comment for ajax process
							window.location = window.location.toString();


						}

						let files = JSON.parse(data.files);

						if (files.files.length > 0 ){

							store.commit('UPDATE_PROJECT_FILES',files.files);

						}
						// Google Tag Manager : push event project send
						//******************************************** */
						gtag_report_event(this.user3dm,'project_form','project_form.save')
						//******************************************** */


					} else {

						this.state = "success"
						store.commit('CHANGE_STEP_PROJECT',10)
						//document.getElementById("client-message").style.display = "none";
						// Google Tag Manager : push event project send
						//******************************************** */
						gtag_report_event(this.user3dm,'project_form','project_form.complete')
						//******************************************** */
					}


				}, (response) => {

					console.log('API Send Project => error',response)

				})

			},
			refreshPage(){

				document.location.href = this.urlNewProject;

			},
		},
		watch: {
			
		}
	}
</script>

<style>

</style>