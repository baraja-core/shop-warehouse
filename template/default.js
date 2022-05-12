Vue.component('cms-warehouse-default', {
	template: `<div class="container-fluid">
	<div class="row mt-2">
		<div class="col">
			<h1>Warehouse</h1>
		</div>
		<div class="col-3 text-right">
			<b-button variant="primary" v-b-modal.modal-new-warehouse>New warehouse</b-button>
		</div>
	</div>
	<div v-if="warehouses === null" class="text-center my-5">
		<b-spinner></b-spinner>
	</div>
	<template v-else>
		<b-card v-if="warehouses.length === 0" class="py-3">
			<div class="m-auto" style="max-width:800px">
				<h2>One tool to manage your warehouses</h2>
				<p>
					Set up all your physical and virtual warehouses
					in one big online system and manage them from one place.
				</p>
				<p>
					You will have real-time information on the status and availability of all products.
					Perform bulk operations of moving products between warehouses,
					ordering, booking on picking.
					This system includes intelligent algorithms for future prediction and capacity planning.
				</p>
				<div class="text-center mt-5">
					<b-button variant="primary" v-b-modal.modal-new-warehouse>Create first warehouse</b-button>
				</div>
			</div>
		</b-card>
		<template v-else>
			<div class="container-fluid">
				<div class="row">
					<div v-for="warehouse in warehouses" class="col-sm-4">
						<b-card>
							<a :href="link('Warehouse:detail', {id: warehouse.id})">{{ warehouse.name }}</a>
						</b-card>
					</div>
				</div>
			</div>
		</template>
	</template>
	<b-modal id="modal-new-warehouse" title="Create warehouse" hide-footer>
		<div v-if="newForm.loading" class="text-center my-5">
			<b-spinner></b-spinner>
		</div>
		<template v-else>
			<b-form @submit="createNewWarehouse">
				<div class="mb-3">
					Name:
					<input v-model="newForm.name" class="form-control">
				</div>
				<b-button type="submit" variant="primary" class="mt-3">Create new warehouse</b-button>
			</b-form>
		</template>
	</b-modal>
</div>`,
	data() {
		return {
			warehouses: null,
			newForm: {
				loading: false,
				name: ''
			}
		};
	},
	created() {
		this.sync();
	},
	methods: {
		sync() {
			axiosApi.get('cms-warehouse')
				.then(req => {
					this.warehouses = req.data.warehouses;
				});
		},
		createNewWarehouse(evt) {
			evt.preventDefault();
			this.newForm.loading = true;
			axiosApi.post('cms-warehouse/create-warehouse', {
				name: this.newForm.name
			}).then(req => {
				this.newForm.name = '';
				this.newForm.loading = false;
				this.sync();
			});
		}
	}
});
