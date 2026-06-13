/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';

const bppPageMetaSettingsPanel = () => {
    // Access post props
    const postType = useSelect(select => select('core/editor').getCurrentPostType());

    // Access meta data
    const [meta, setMeta] = useEntityProp('postType', postType, 'meta');

    // Make sure we are in the right post type
    if ('page' !== postType) {
        return '';
    }

    const ALLOWED_MEDIA_TYPES = ['image'];

    const { _bpp_page_image: mediaUrl = '' } = meta;

    return (
        <PluginDocumentSettingPanel name='bpp-page-settings-panel' title='Page Settings' className='bpp-page-settings'>
            <MediaUploadCheck>
                <MediaUpload
                    onSelect={media => setMeta({ ...meta, _bpp_page_image: media.url })}
                    allowedTypes={ALLOWED_MEDIA_TYPES}
                    value={mediaUrl}
                    render={({ open }) => (
                        <Button onClick={open} className='button is-primary'>
                            {mediaUrl ? __('Change Image', 'bp-plugin') : __('Set Image', 'bp-plugin')}
                        </Button>
                    )}
                />
                {mediaUrl && (
                    <Button
                        className='is-link is-destructive'
                        onClick={() => setMeta({ ...meta, _bpp_page_image: '' })}
                    >
                        {__('Remove Image', 'bp-plugin')}
                    </Button>
                )}
            </MediaUploadCheck>
        </PluginDocumentSettingPanel>
    );
};

export default bppPageMetaSettingsPanel;
