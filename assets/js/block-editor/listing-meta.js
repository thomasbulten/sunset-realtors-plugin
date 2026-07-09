/**
 * Sunset listing settings panel (Block Editor).
 * @param {Object} wp     WordPress object.
 * @param {Object} config Sunset plugin configuration.
 */
(function (wp, config) {
	const { registerPlugin } = wp.plugins;
	const { PluginDocumentSettingPanel } = wp.editPost;
	const { SelectControl, PanelRow } = wp.components;
	const { __ } = wp.i18n;
	const { useSelect, useDispatch } = wp.data;
	const { createElement: el } = wp.element;

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

		const assignedEmployee = meta[config.meta.assignedEmployee] ?? 0;
		const priceCurrency = meta[config.meta.priceCurrency] || 'EUR';

		const employeeValue =
			assignedEmployee && Number(assignedEmployee) > 0
				? String(assignedEmployee)
				: '';

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
				el(SelectControl, {
					label: __('Makelaar', 'sunset-realtors-plugin'),
					value: employeeValue,
					options: [
						{
							label: __('Standaard', 'sunset-realtors-plugin'),
							value: '',
						},
						...(config.employees || []),
					],
					onChange: (value) => {
						setMetaValue(
							config.meta.assignedEmployee,
							value ? parseInt(value, 10) : 0,
						);
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
