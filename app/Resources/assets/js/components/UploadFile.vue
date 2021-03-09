<template>
	<div>
		<div id="upload-file" class="col-sm-12 bg-white pad-15 mrg-t-40">
            <h2></h2>
	    	<div class="text-center align-center">
	    		<i class="fas fa-check-circle fa-5x mrg-15-0" v-show="processing==2"></i>
	    		<div class="loader" v-show="processing > 0 && processing < 2"></div>
	    		<p>
	    			<span v-show="processing > 0 && processing < 2">Chargement du fichier sur notre plateforme, Merci de patienter … </span><br>
	    			<!-- <span v-if="processing > 0 && processing < 2">Envoi en cours</span> 
	    			{{fileUploaded}}/{{ numberFile }} fichier(s) -->
	    			<span v-show="processing==2">Vos fichiers ont bien été envoyés sur notre serveur.</span> 
	    			<!-- <span v-if="processing > 0 && processing < 2">Merci de patientier</span> -->
	    		</p>
	    	</div>
	    	<div class="col-sm-12 text-center align-center">
				<!-- <button v-on:click="uploadAllFiles" class="btn btn-default btn-rounded"> > TEST upload DB</button> -->
			</div>
    	</div>
    </div>
</template>


<script>
	
	import Vue from 'vue'
	import { mapGetters } from 'vuex'
	import store from '../stores/PrintStore'


	export default {
		name: "uploadFile",
		store: store,
		props: [
			'apiUpload',
		],
		data: function(){
			return {
				processing: 0
			}
		},
		component: {
	    },
		mounted (){
			/*console.log('Tab files mounted => ', this.fileTabName)
			this.uploader.on('submitted', id => {
		    	const submittedFiles = this.state.submittedFiles
		    	submittedFiles.push(id)
		    	this.$set(this.state, 'submittedFiles', submittedFiles)
		    })*/
		},
		computed: {
			...mapGetters([
				'print3dFiles',
				'stepFormProcess',
				'uploadProcess',
			]),
			fileTabName: function(){

				let tabFiles = []

				for(const index in this.print3dFiles){

					if(this.print3dFiles[index].uploaded == 0){
						tabFiles[index] = this.print3dFiles[index].file
					}

				}

				return tabFiles
			},
			numberFile: function(){

				return this.print3dFiles.length

			},
			fileUploaded: function(){

				let uploaded = 0

				for(const index in this.print3dFiles){

					if(this.print3dFiles[index].uploaded == 1){
						uploaded ++
					}

				}

				return uploaded

			},
			isAllUploaded: function(){



			},
		},
		methods: {
			uploadAllFiles(){

				console.log('NB to WILL UPLOAD',this.fileTabName.length)

				if(this.fileTabName.length >= 1){

					store.commit('UPDATE_PROCESS_UPLOAD',{'state': false})
					console.log('List File', this.fileTabName)

					this.processing = 1


					for(const file in this.fileTabName){

						var formData = new FormData();
						formData.append('image', this.fileTabName[file]);

						this.$http.post(this.apiUpload,formData).then( function(response){ 

			                

			                store.commit('UPDATE_3DFILE_FILE_DB',{ 'productId':file , 'fileDb': response.body.file_db})

	

						    this.fileTabName.splice(file, 1)
				            //this.fileUploaded ++
				            if(this.fileTabName.length == 0){
				             	this.processing = 2
								store.commit('UPDATE_PROCESS_UPLOAD',{'state': true})
								store.commit('CHANGE_STEP',8)
		  						setTimeout(function() {$('html,body').animate({scrollTop: $('#payment').offset().top},'slow');}, 500)
				            }

			            }, function (response) {

			            	//console.log(response)

			             });

					}

				} else {

					console.log('Upload Already Yet')
					store.commit('UPDATE_PROCESS_UPLOAD',{'state': true})
					store.commit('CHANGE_STEP',8)
	  				setTimeout(function() {$('html,body').animate({scrollTop: $('#payment').offset().top},'slow');}, 500)

				}

			},

		},
		watch: {
		    stepFormProcess: function (val) {
		      if(val == 7){
		      	this.uploadAllFiles()
		      }
		    },
		}
	}
</script>

<style>

</style>