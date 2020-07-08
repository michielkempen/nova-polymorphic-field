<template>
	<div>

		<default-field :field="field" v-show="shouldShowTypeSelect">
			<template slot="field">
				<select
					:id="field.attribute"
					v-model="value"
					class="w-full form-control form-select"
					:class="errorClasses"
					:disabled="shouldDisableTypeSelect"
				>
					<option value="" selected :disabled="!field.nullable">
						{{field.nullable ? '—' : __('Choose an option')}}
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

		computed: {

			/**
			 * Do not show the type select option if this is the edit screen
			 * And we don't want the user to change the polymorphic type.
			 */
			shouldShowTypeSelect() {
				return !(this.resourceId && this.field.hideTypeWhenUpdating)
			},

			/**
			 * Do not show the type select option if this is the edit screen
			 * And we don't want the user to change the polumorphic type.
			 */
			shouldDisableTypeSelect() {
				return this.resourceId && this.field.disableTypeWhenUpdating
			},

		},

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
