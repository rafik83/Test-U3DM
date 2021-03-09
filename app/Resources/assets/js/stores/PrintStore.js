import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const state = {
	unity: 'mm',
	fileNumber: 0,
	stepFormProcess : 1, // 1 : file // 2 : Maker // 3 : Shipping // 4 : Account // 5 : Address // 6 : Summary // 7 : Payment
	stepFormProject : 1, // 1 : Open project
	print3dFiles : [],
	makersList: [],
	makerSelected: {},
	shippingSelected: {}, // { 'type' : 'key', 'price' : '1500',  }
	fees: {},
	conditionValidate : false,
	user3dm: {},
	currentBasket: {},
	uploadProcess: false,
	orderId: false,
	addressBilling:{},
	addressShipping:{},
	instruction:false,
	tmpPrice: 0,
	tmpPriceFinition:0,
	coupon: {},
	discount_excl: 0,
	discount_incl: 0,
	user_pickup_address : false,
	expiredDateValidity: false,
	saveTimeStamp: 0,
	projectStore: {
		id: null,
		/*'fields':[],
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
		'projectType': '',
		'deliveryTime': '',
		'file':'',*/
	},
	projectOrigin: {

	},
	projectFiles:[],
	urlNewProject: '',
	stripeIntentId: false,
	stripeIntentSecret: false,
	totalTtc: 0,

}
const mutations = {

	CHANGE_UNITY: (state, newUnity) => {
		state.unity = newUnity
	},
	CHANGE_STEP: (state, newStep) => {
		state.stepFormProcess = newStep
	},
	CHANGE_STEP_PROJECT: (state, newStep) => {
		state.stepFormProject = newStep
	},
	ADD_PRINT_3DFILE: (state, print3dFilesObject) => {

		print3dFilesObject.fileNumber = state.fileNumber
		state.print3dFiles.push(
			print3dFilesObject
		)
		state.fileNumber ++

	},
	UPDATE_3DFILE_QUANTITY: (state, data) =>{
		state.print3dFiles[data.productId].quantity = data.quantity
	},
	UPDATE_3DFILE_TECHNOLOGY: (state, data) => {
		state.print3dFiles[data.productId].technology = data.technology
		state.print3dFiles[data.productId].technologyLabel = data.label
	},
	UPDATE_3DFILE_MATERIAL: (state, data) => {
		state.print3dFiles[data.productId].material = data.material
		state.print3dFiles[data.productId].materialLabel = data.label
	},
	UPDATE_3DFILE_COLOR: (state, data) => {
		state.print3dFiles[data.productId].color = data.color
	},
	UPDATE_3DFILE_LAYER: (state, data) => {
		state.print3dFiles[data.productId].layer = data.layer
	},
	UPDATE_3DFILE_FILLING: (state, data) => {
		state.print3dFiles[data.productId].filling = data.filling
	},
	UPDATE_3DFILE_PRICES: (state, data) =>{
		state.print3dFiles[data.productId].priceTaxEcl = data.priceTaxEcl
		state.print3dFiles[data.productId].priceTaxEclUnit = data.priceTaxEclUnit
		state.print3dFiles[data.productId].priceTaxEclDisplay = data.priceTaxEclDisplay
		state.print3dFiles[data.productId].priceTaxEclUnitDisplay = data.priceTaxEclUnitDisplay
		state.print3dFiles[data.productId].priceTaxInc = data.priceTaxInc
		state.print3dFiles[data.productId].priceTaxIncUnit = data.priceTaxIncUnit
		state.print3dFiles[data.productId].finishing = data.finishing

	},
	UPDATE_3DFILE_DIMENSION: (state, data) => {
		state.print3dFiles[data.productId].dimensions.x = data.data.x
		state.print3dFiles[data.productId].dimensions.y = data.data.y
		state.print3dFiles[data.productId].dimensions.z = data.data.z
		state.print3dFiles[data.productId].volume = data.data.volume
	},
	UPDATE_3DFILE_FILE_NAME: (state, data) => {
		state.print3dFiles[data.productId].filename = data.filename
	},
	UPDATE_3DFILE_FILE: (state, data) => {
		state.print3dFiles[data.productId].file = data.file
	},
	UPDATE_3DFILE_FILE_DB: (state, data) => {
		state.print3dFiles[data.productId].fileDb = data.fileDb
		state.print3dFiles[data.productId].uploaded = 1
	},
	UPDATE_3DFILE_STATE: (state, data) => {
		state.print3dFiles[data.productId].state = data.state
	},
	UPDATE_3DFILE_PRICE_MAKER: (state,data) =>{
		state.print3dFiles[data.productId].makersList = data.makers
	},
	UPDATE_TMP_CART: (state,data) =>{
		state.print3dFiles[data.productId].tmpPrice = data.price
		state.print3dFiles[data.productId].tmpPriceFinition = data.priceFinition

		store.calculateTmpPrice(state)
	},
	MAKE_MAKER_LIST: (state, makers) => {
		state.makersList = makers
	},
	RESET_MAKER: (state) => {
		state.makersList = []
	},
	ADD_DISTANCE_MAKER: (state,data) => {
		state.makersList[data.makerIndex].pickup.distance = data.distance
	},
	MAKER_SELECT: (state, maker) => {
		state.makerSelected = maker
	},
	ADD_SHIPPING: (state, shippingObj) =>{
		state.shippingSelected = shippingObj
	},
	REMOVE_SHIPPING: (state, shippingObj) =>{
		state.shippingSelected = {}
	},
	'SET_FEES': (state, feesObj) =>{
		state.fees = feesObj
	},
	'TOGGLE_CONDITION_VALIDATE': (state, data) => {
		state.conditionValidate = data
	},
	'UPDATE_USER_3DM': (state, data) => {
		state.user3dm = data
	},
	'UPDATE_CURRENT_BASKET': (state, data) => {
		state.currentBasket = data
	},
	'UPDATE_PROCESS_UPLOAD': (state, data) => {
		state.uploadProcess = data.state
	},
	'UPDATE_ORDER_ID': (state, data) => {
		state.orderId = data
	},
	'DELETE_PRODUCT': (state,data) => {
		//console.log('index delete',data.productId)

		state.print3dFiles.splice(data.productId, 1)

		for(const index in state.print3dFiles){

			state.print3dFiles[index].fileNumber = index 

		}

		state.fileNumber = state.print3dFiles.length 

		if(state.fileNumber == 0){

			state.makersList = []

		}

		store.calculateTmpPrice(state)
	},
	'DELETE_ITEM_BASKET': (state,data) => {
		//console.log('index delete',data)

		//var index = data.currentBasket.indexOf(data);

		state.currentBasket.splice(data, 1)
	},
	'SAVE_ADDRESS_BILLING': (state,data) => {
		state.addressBilling = data
	},
	'SAVE_ADDRESS_SHIPPING': (state,data) => {
		state.addressShipping = data
	},
	'ADD_INSTRUCTION': (state,data) => {
		state.instruction = data
	},
	'ADD_COUPON': (state,data) => {
		state.coupon.name = data.name
		state.coupon.code = data.code
		state.discount_excl = data.discount_excl
		state.discount_incl = data.discount_incl
	},
	'REMOVE_COUPON': (state) => {
		state.coupon = {}
		state.discount_excl = 0
		state.discount_incl = 0
	},
	'UPDATE_USER_PICKUP_ADDRESS': (state,data) => {
		state.user_pickup_address = data
	},
	'UPDATE_PROJECT': (state,data) => {
		state.projectStore.id = data.id
		state.projectStore.fields = data.fields
		state.projectStore.name = data.name
		state.projectStore.skills = data.skills
		state.projectStore.softwares = data.softwares
		state.projectStore.description = data.description
		state.projectStore.dim = {}
		state.projectStore.dim.x = data.dim.x
		state.projectStore.dim.y = data.dim.y
		state.projectStore.dim.z = data.dim.z
		state.projectStore.address = {}
		state.projectStore.address.street1 = data.address.street1
		state.projectStore.address.street2 = data.address.street2
		state.projectStore.address.zipcode = data.address.zipcode
		state.projectStore.address.firstname = data.address.firstname
		state.projectStore.address.lastname = data.address.lastname
		state.projectStore.address.city = data.address.city
		state.projectStore.address.country = data.address.country
		state.projectStore.address.telephone = data.address.telephone
		state.projectStore.scanOnSite = data.scanOnSite
		state.projectStore.projectType = data.projectType
		state.projectStore.deliveryTime = data.deliveryTime
		state.projectStore.file = data.file;
		state.projectStore.reference = data.reference;
	},
	'UPDATE_PROJECT_REFERENCE': (state,data) => {
		state.projectStore.reference = data;
	},
	'UPDATE_PROJECT_ID': (state,data) => {
		state.projectStore.id = data;
	},
	'UPDATE_PROJECT_FILES': (state,data) => {
		state.projectFiles = data;
	},
	'FILL_ORIGIN_PROJECT': (state,data) => {
		state.projectOrigin = data
	},
	'SET_URL_NEW_PROJECT': (state,data) => {
		state.urlNewProject = data
	},
	'SET_EXPIRED_DATE_VALIDITY': (state,data) => {
		state.expiredDateValidity = data
	},
	'SET_SAVE_TIMESTAMP': (state) => {
		state.saveTimeStamp = Date.now()
	},
	'SET_STRIPE_INTENT_ID': (state,data) => {
		state.stripeIntentId = data
	},
	'SET_STRIPE_INTENT_SECRET': (state,data) => {
		state.stripeIntentSecret = data
	},
	'SET_TOTAL_TTC': (state,data) => {
		state.totalTtc = data
	}
}

const actions = {
	/*deleteProduct({ commit },data){

		commit('DELETE_PRODUCT',data)

	},*/

}

const getters = {

	stepFormProcess: state => state.stepFormProcess,

	stepFormProject: state => state.stepFormProject,

	print3dFiles : state => state.print3dFiles,

	fileNumber: state => state.fileNumber,

	makersList: state => state.makersList,

	makerSelected: state => state.makerSelected,

	shippingSelected: state => state.shippingSelected,

	fees: state => state.fees,

	tmpPrice: state => state.tmpPrice,

	tmpPriceFinition: state => state.tmpPriceFinition,

	conditionValidate: state => state.conditionValidate,

	user3dm: state => state.user3dm,

	currentBasket: state => state.currentBasket,

	uploadProcess: state => state.uploadProcess,

	orderId: state => state.orderId,

	addressShipping: state => state.addressShipping,

	addressBilling: state => state.addressBilling,

	instruction: state => state.instruction,

	coupon: state => state.coupon,

	discount_excl: state => state.discount_excl,

	discount_incl: state => state.discount_incl,

	user_pickup_address: state => state.user_pickup_address,

	projectStore: state => state.projectStore,

	projectOrigin: state => state.projectOrigin,

	projectFiles: state => state.projectFiles,

	urlNewProject: state => state.urlNewProject,

	expiredDateValidity: state => state.expiredDateValidity,

	saveTimeStamp: state => state.saveTimeStamp,

	stripeIntentId: state => state.stripeIntentId,

	stripeIntentSecret: state => state.stripeIntentSecret,

	totalTtc: state => state.totalTtc,

}

let store = new  Vuex.Store({

	state: state,

	mutations: mutations,

	getters: getters,

	actions: actions,

	strict: true,
	
})

store.calculateTmpPrice = state => {

	let price = 0
	let priceFinition = 0

	state.print3dFiles.forEach(function(product) {
  
		//console.log('Array 3D File =>',product.tmpPrice)

		price += product.tmpPrice
		priceFinition += product.tmpPriceFinition

	});

	state.tmpPrice = price
	state.tmpPriceFinition = priceFinition

};


//global.store = store

export default store



