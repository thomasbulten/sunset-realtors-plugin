/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import bppPageMetaSettingsPanel from './meta-settings/index.js';

registerPlugin('bpp-page-settings-panel', {
    icon: 'id-alt',
    render: bppPageMetaSettingsPanel,
});
