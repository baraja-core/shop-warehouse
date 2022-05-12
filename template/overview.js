Vue.component('cms-warehouse-overview', {
	props: ['id'],
	template: `<cms-card>
		<div v-if="warehouse === null" class="text-center my-5">
			<b-spinner></b-spinner>
		</div>
		<template v-else>
			{{ warehouse }}
			<b-form @submit="save">
				<div class="container-fluid">
					<div class="row">
						<div class="col">
							<div class="row">
								<div class="col">
									Name:
									<b-form-input v-model="warehouse.name"></b-form-input>
								</div>
								<div class="col">
									Default minimal quantity:
									<b-form-input type="number" v-model="warehouse.defaultMinimalQuantity"></b-form-input>
								</div>
								<div class="col">
									Quantity can be negative?<br>
									<b-form-checkbox v-model="warehouse.quantityCanBeNegative"></b-form-checkbox>
								</div>
							</div>
							<div class="row">
								<div class="col">
									<cms-editor v-model="warehouse.description" rows="2"></cms-editor>
								</div>
							</div>
						</div>
						<div class="col-sm-3">
							Map<br>
							<img src="https://maps.googleapis.com/maps/api/staticmap?center=51.477222,0&zoom=14&size=400x400&key=AIzaSyA3kg7YWugGl1lTXmAmaBGPNhDW9pEh5bo&signature=ciftxSv4681tGSAnes7ktLrVI3g=">
							{{ warehouse.location }}<br>
							{{ warehouse.longitude }}<br>
							{{ warehouse.latitude }}

							Location:
							<b-form-input v-model="warehouse.location"></b-form-input>
						</div>
					</div>
				</div>
				<b-button type="submit" variant="primary" class="mt-3">
					<template v-if="loading"><b-spinner small></b-spinner></template>
					<template v-else>Save</template>
				</b-button>
			</b-form>
		</template>
	</cms-card>`,
	data() {
		return {
			warehouse: null,
			loading: false
		};
	},
	created() {
		this.sync();
	},
	methods: {
		sync() {
			axiosApi.get('cms-warehouse/detail?id=' + this.id)
				.then(req => {
					this.warehouse = req.data;
				});
		},
		save(evt) {
			evt.preventDefault();
			this.loading = true;
			axiosApi.post('cms-warehouse/save-detail', {
				id: this.id,
				name: this.warehouse.name,
				description: this.warehouse.description,
				location: this.warehouse.location,
				defaultMinimalQuantity: this.warehouse.defaultMinimalQuantity,
				quantityCanBeNegative: this.warehouse.quantityCanBeNegative
			}).then(req => {
				this.loading = false;
				this.sync();
			});
		}
	}
});
