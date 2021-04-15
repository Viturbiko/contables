<template>

	<div>

		<table class="table-auto w-full mt-4">

			<thead class="bg-blue-900 border border-blue-900">

				<tr>
					
					<th class="text-white p-2" v-for="heading in headings"> {{ heading }}</th>

					<th class="text-white p-2"></th>

				</tr>

			</thead>

			<tbody>
				
				<tr v-for="item in data" class="p-4">

					<td class="p-4 border border-gray-400" v-for="row in item">{{ row }}</td>

					<td class="p-4 border border-gray-400 w-64 text-center">

						<button class="bg-transparent hover:bg-blue-900 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded">Editar</button>

						<button class="bg-transparent hover:bg-red-900 text-red-700 font-semibold hover:text-white py-2 px-4 border border-red-500 hover:border-transparent rounded">Eliminar</button>

					</td>

				</tr>

			</tbody>

		</table>

	</div>

</template>

<script>
export default {

	props: [
		'headings',
		'data'
	]

}
</script>

<style lang="css" scoped>
</style>