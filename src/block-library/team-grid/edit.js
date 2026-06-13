/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { PanelBody, Disabled, BaseControl, SelectControl } from '@wordpress/components';

/**
 * External dependencies
 */
import classnames from 'classnames';

function Edit({ attributes, setAttributes }) {
    const { order = 'ASC', orderBy = 'date', className = '' } = attributes;

    const classes = classnames(['bpp-team-grid', className]);

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Display')} initialOpen={true}>
                    <BaseControl>
                        <SelectControl
                            label={__('Order by:')}
                            value={orderBy}
                            onChange={value => setAttributes({ orderBy: value })}
                            options={[
                                { value: 'date', label: 'Date' },
                                { value: 'rand', label: 'Random' },
                                { value: 'title', label: 'Title' },
                            ]}
                        />
                    </BaseControl>
                    <BaseControl>
                        <SelectControl
                            label={__('Order')}
                            value={order}
                            onChange={value => setAttributes({ order: value })}
                            options={[
                                { value: 'ASC', label: 'Ascendant' },
                                { value: 'DESC', label: 'Descendent' },
                            ]}
                        />
                    </BaseControl>
                </PanelBody>
            </InspectorControls>
            <div {...useBlockProps({ className: classes })}>
                <Disabled>
                    <ServerSideRender block='bpp/team-grid' attributes={{ ...attributes }} />
                </Disabled>
            </div>
        </>
    );
}

export default Edit;
