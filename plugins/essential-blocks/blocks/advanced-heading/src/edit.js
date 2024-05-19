/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import { useEffect } from "@wordpress/element";
import {
    BlockControls,
    AlignmentToolbar,
    RichText,
    useBlockProps,
} from "@wordpress/block-editor";
import { select } from "@wordpress/data";
import { useEntityProp, store as coreStore } from '@wordpress/core-data';

/**
 * Internal depencencies
 */
import classnames from "classnames";

import Inspector from "./inspector";

/**
 * External depencencies
 */
const {
    duplicateBlockIdFix,
    DynamicInputValueHandler,
    EBDisplayIcon
} = window.EBControls;

import Style from "./style";

export default function Edit(props) {
    const {
        attributes,
        setAttributes,
        className,
        clientId,
        isSelected,
        name
    } = props;
    const {
        resOption,
        blockId,
        blockMeta,
        preset,
        align,
        tagName,
        titleText,
        subtitleTagName,
        subtitleText,
        displaySubtitle,
        displaySeperator,
        seperatorPosition,
        seperatorType,
        separatorIcon,
        classHook,
        source,
        currentPostId,
        currentPostType
    } = attributes;

    // this useEffect is for creating a unique id for each block's unique className by a random unique number
    useEffect(() => {
        const BLOCK_PREFIX = "eb-advance-heading";
        duplicateBlockIdFix({
            BLOCK_PREFIX,
            blockId,
            setAttributes,
            select,
            clientId,
        });

    }, []);

    useEffect(() => {
        const postId = select("core/editor").getCurrentPostId();
        const postType = select("core/editor").getCurrentPostType();

        if (postId) {
            setAttributes({
                currentPostId: postId,
                currentPostType: postType,
            });
        }
    }, [source])

    const blockProps = useBlockProps({
        className: classnames(className, `eb-guten-block-main-parent-wrapper`),
    });

    const [rawTitle = '', setTitle, fullTitle] = useEntityProp(
        'postType',
        currentPostType,
        'title',
        currentPostId
    );

    let TagName = tagName;

    return (
        <>
            {isSelected && (
                <>
                    <BlockControls>
                        <AlignmentToolbar
                            value={align}
                            onChange={(align) => setAttributes({ align })}
                            controls={["left", "center", "right"]}
                        />
                    </BlockControls>
                    <Inspector
                        attributes={attributes}
                        setAttributes={setAttributes}
                    />
                </>
            )}

            <div {...blockProps}>
                <Style {...props} />

                {source == 'dynamic-title' && currentPostId == 0 && (
                    <div className="eb-loading" >
                        <img src={`${EssentialBlocksLocalize?.image_url}/ajax-loader.gif`} alt="Loading..." />
                    </div >
                )}

                {((source == 'dynamic-title' && currentPostId != 0) || source == 'custom') && (
                    <>
                        <div
                            className={`eb-parent-wrapper eb-parent-${blockId} ${classHook}`}
                        >
                            <div
                                className={`eb-advance-heading-wrapper ${blockId} ${preset}`}
                                data-id={blockId}
                            >
                                {displaySeperator && seperatorPosition === "top" && (
                                    <div className={"eb-ah-separator " + seperatorType}>
                                        {seperatorType === "icon" && (
                                            // <i
                                            //     className={`${separatorIcon
                                            //         ? separatorIcon
                                            //         : "fas fa-arrow-circle-down"
                                            //         }`}
                                            // ></i>
                                            <EBDisplayIcon icon={separatorIcon} />
                                        )}
                                    </div>
                                )}

                                {source == 'dynamic-title' && (
                                    <>
                                        {currentPostId > 0 && (
                                            <DynamicInputValueHandler
                                                value={rawTitle}
                                                tagName={tagName}
                                                className="eb-ah-title"
                                                allowedFormats={[
                                                    "core/bold",
                                                    "core/italic",
                                                    "core/link",
                                                    "core/strikethrough",
                                                    "core/underline",
                                                    "core/text-color",
                                                ]}
                                                onChange={setTitle}
                                                readOnly={true}
                                            />
                                        )}

                                        {/* for FSE */}
                                        {typeof currentPostId == 'string' && (
                                            <TagName>
                                                {rawTitle ? rawTitle : __('Title')}
                                            </TagName>
                                        )}
                                    </>

                                )}

                                {source == 'custom' && (
                                    <DynamicInputValueHandler
                                        value={titleText}
                                        tagName={tagName}
                                        className="eb-ah-title"
                                        allowedFormats={[
                                            "core/bold",
                                            "core/italic",
                                            "core/link",
                                            "core/strikethrough",
                                            "core/underline",
                                            "core/text-color",
                                        ]}
                                        onChange={(titleText) =>
                                            setAttributes({ titleText })
                                        }
                                        readOnly={true}
                                    />
                                )}

                                {source == 'custom' && displaySubtitle && (
                                    <DynamicInputValueHandler
                                        tagName={subtitleTagName}
                                        className="eb-ah-subtitle"
                                        value={subtitleText}
                                        allowedFormats={[
                                            "core/bold",
                                            "core/italic",
                                            "core/link",
                                            "core/strikethrough",
                                            "core/underline",
                                            "core/text-color",
                                        ]}
                                        onChange={(subtitleText) =>
                                            setAttributes({ subtitleText })
                                        }
                                        readOnly={true}
                                    />
                                )}
                                {displaySeperator && seperatorPosition === "bottom" && (
                                    <div className={"eb-ah-separator " + seperatorType}>
                                        {seperatorType === "icon" && (
                                            // <i
                                            //     className={`${separatorIcon
                                            //         ? separatorIcon
                                            //         : "fas fa-arrow-circle-down"
                                            //         }`}
                                            // ></i>
                                            <EBDisplayIcon icon={separatorIcon} />
                                        )}
                                    </div>
                                )}
                            </div>
                        </div>
                    </>
                )}
            </div>
        </>
    );
}
