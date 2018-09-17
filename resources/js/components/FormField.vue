<template>
	<div>

		<default-field :field="field">
			<template slot="field">
				<select
					:id="field.attribute"
					v-model="value"
					class="w-full form-control form-select"
					:class="errorClasses"
				>
					<option value="" selected disabled>
						{{__('Choose an option')}}
					</option>

					<option
						v-for="option in field.types"
						:value="option.value"
						:selected="option.value == value"
					>
						{{ option.label }}
					</option>
				</select>

				<p v-if="hasError" class="my-2 text-danger">
					{{ firstError }}
				</p>
			</template>
		</default-field>

		<div v-for="type in field.types" v-if="value === type.value">
			<div v-for="typeField in type.fields" :ref="'type-'+type.value+'-fields'">
				<component
					:is="'form-' + typeField.component"
					:resource-id="resourceId"
					:resource-name="resourceName"
					:field="typeField"
					:ref="'field-' + typeField.attribute"
					:errors="errors"
				/>
			</div>
		</div>

	</div>
</template>

<script>
	import {FormField, HandlesValidationErrors} from 'laravel-nova'

	export default {
		mixins: [FormField, HandlesValidationErrors],

		props: ['resourceName', 'resourceId', 'field'],

		methods: {

			fill(formData) {
				formData.append(this.field.attribute, this.value)

				this.$children.forEach(component => {
					if(component.field.attribute !== this.field.attribute) {
						component.field.fill(formData);
					}
				})
			}

		}
	}
</script>
