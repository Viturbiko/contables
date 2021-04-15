<template>

	<div>

		<h2 class="h2">Cuentas</h2>

		<simple-table :headings="headings" :data="accounts"></simple-table>

	</div>

</template>

<script>
import SimpleTable from './../../components/tables/simple-table';
import gql from 'graphql-tag';

export default {
	mounted() {
		this.getAccounts();
	},
	data() {
		return {
			headings: [
				'ID',
				'Nombre'
			],
			accounts: []
		}
	},
	components: {
		SimpleTable
	},
	methods: {
		async getAccounts() {
			const response = await this.$apollo.query({
				query: gql(`
					{
						accounts(first: 20) {
							data {
								id
								name
							}
						}
					}
				`)
			});

			this.accounts = response.data.accounts.data.map(item => {
				return {
					id: item.id,
					name: item.name
				}
			});
		}

	}
}
</script>

<style lang="css" scoped>
</style>