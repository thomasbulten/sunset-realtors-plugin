/**
 * Sunset listing settings panel (Block Editor).
 * @param {Object} wp     WordPress object.
 * @param {Object} config Sunset plugin configuration.
 */
(function (wp, config) {
	const { registerPlugin } = wp.plugins;
	const { PluginDocumentSettingPanel } = wp.editPost;
	const { SelectControl, CheckboxControl, TextControl, PanelRow } = wp.components;
	const { __ } = wp.i18n;
	const { useSelect, useDispatch } = wp.data;
	const { createElement: el } = wp.element;

	function normalizeAssignedEmployeeIds(value) {
		if (Array.isArray(value)) {
			return value
				.map((id) => parseInt(id, 10))
				.filter((id) => Number.isInteger(id) && id > 0);
		}

		const singleId = parseInt(value, 10);

		return Number.isInteger(singleId) && singleId > 0 ? [singleId] : [];
	}

	function SunsetListingSettings() {
		const postType = useSelect(
			(select) => select('core/editor').getCurrentPostType(),
			[],
		);

		const meta = useSelect(
			(select) =>
				select('core/editor').getEditedPostAttribute('meta') || {},
			[],
		);

		const { editPost } = useDispatch('core/editor');

		if (postType !== config.postType) {
			return null;
		}

		const assignedEmployees = normalizeAssignedEmployeeIds(
			meta[config.meta.assignedEmployee],
		);
		const priceCurrency = meta[config.meta.priceCurrency] || 'EUR';
		const matterportId = meta[config.meta.matterportId] || '';

		const setMetaValue = (key, value) => {
			editPost({
				meta: {
					...meta,
					[key]: value,
				},
			});
		};

		return el(
			PluginDocumentSettingPanel,
			{
				name: 'sunset-listing-settings',
				title: __('Sunset instellingen', 'sunset-realtors-plugin'),
				className: 'sunset-listing-settings',
			},
			el(
				PanelRow,
				null,
				el(
					'div',
					{ className: 'sunset-listing-settings__employees' },
					el(
						'p',
						{ className: 'components-base-control__label' },
						__('Makelaar', 'sunset-realtors-plugin'),
					),
					el(
						'div',
						{
							className:
								'sunset-listing-settings__employees-list',
						},
						...(config.employees || []).map((employee) => {
							const employeeId = parseInt(employee.value, 10);

							return el(CheckboxControl, {
								key: employee.value,
								label: employee.label,
								checked: assignedEmployees.includes(employeeId),
								onChange: (checked) => {
									const nextValue = checked
										? [...assignedEmployees, employeeId]
										: assignedEmployees.filter(
												(id) => id !== employeeId,
											);

									setMetaValue(
										config.meta.assignedEmployee,
										nextValue,
									);
								},
							});
						}),
					),
				),
			),
			el(
				PanelRow,
				null,
				el(TextControl, {
					label: __('Matterport ID', 'sunset-realtors-plugin'),
					value: matterportId,
					onChange: (value) => {
						setMetaValue(config.meta.matterportId, value);
					},
				}),
			),
			el(
				PanelRow,
				null,
				el(SelectControl, {
					label: __('Basismunt', 'sunset-realtors-plugin'),
					value: priceCurrency,
					options: (config.currencies || []).map((currency) => ({
						label: currency,
						value: currency,
					})),
					onChange: (value) => {
						setMetaValue(config.meta.priceCurrency, value);
					},
				}),
			),
		);
	}

	registerPlugin('sunset-listing-settings', {
		render: SunsetListingSettings,
	});
})(window.wp, window.sunsetListingMeta || {});
