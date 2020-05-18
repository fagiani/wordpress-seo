/* External dependencies */
import { Fragment } from "@wordpress/element";
import { Collapsible } from "@yoast/components";

/* Internal dependencies */
import FacebookContainer from "../../containers/FacebookEditor";
import TwitterContainer from "../../containers/TwitterEditor";

/**
 * Component that renders the social metadata collapsibles.
 *
 * @returns {React.Component} The social metadata collapsibles.
 */
const SocialMetadata = () => {
	return (
		<Fragment>
			<Collapsible
				hasPadding={ true }
				hasSeparator={ true }
				title="Facebook preview"
				initialIsOpen={ true }
			>
				<FacebookContainer />
			</Collapsible>
			<Collapsible
				hasPadding={ true }
				hasSeparator={ true }
				title="Twitter preview"
				initialIsOpen={ true }
			>
				<TwitterContainer />
			</Collapsible>
		</Fragment>
	);
};

export default SocialMetadata;
