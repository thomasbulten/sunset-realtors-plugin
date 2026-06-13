/**
 * WordPress dependencies
 */
import { useEntityRecords } from '@wordpress/core-data';

function usePostList(postType = 'team_member', perPage = -1) {
	const query = { hide_empty: true, per_page: perPage };
	const params = ['postType', postType, query];
	const { records, isResolving } = useEntityRecords(...params);

	const isLoading = isResolving;
	const postList = [{ label: 'Select team member', value: 0 }];

	if (records) {
		records.map((post) => {
			return postList.push({
				label: post.title.rendered,
				value: post.id,
			});
		});
	}

	return { postList, isLoading };
}

export default usePostList;
