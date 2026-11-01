
import { createReduxStore, register, select } from '@wordpress/data';
const DEFAULT_STATE = {
	showTopBar: true,
	showAuthorBar: true,
	showStatsBar: true,
	showLastUpdated: true,
	buttonType: 'github',
};

const actions = {
	setShowTopBar( showTopBar ) {
		return {
			type: 'SET_SHOW_TOP_BAR',
			showTopBar,
		};
	},
	setShowAuthorBar( showAuthorBar ) {
		return {
			type: 'SET_SHOW_AUTHOR_BAR',
			showAuthorBar,
		};
	},
	setShowStatsBar( showStatsBar ) {
		return {
			type: 'SET_SHOW_STATS_BAR',
			showStatsBar,
		};
	},
	setShowLastUpdated( showLastUpdated ) {
		return {
			type: 'SET_SHOW_LAST_UPDATED',
			showLastUpdated,
		};
	},
	setButtonType( buttonType ) {
		return {
			type: 'SET_BUTTON_TYPE',
			buttonType,
		};
	},
};

const createBlockStore = ( uniqueId ) => {
	return createReduxStore( `dlxplugins/github-grid-block/${ uniqueId }`, {
		reducer( state = DEFAULT_STATE, action ) {
			switch ( action.type ) {
				case 'SET_SHOW_TOP_BAR':
					return {
						...state,
						showTopBar: action.showTopBar,
					};
				case 'SET_SHOW_AUTHOR_BAR':
					return {
						...state,
						showAuthorBar: action.showAuthorBar,
					};
				case 'SET_SHOW_STATS_BAR':
					return {
						...state,
						showStatsBar: action.showStatsBar,
					};
				case 'SET_SHOW_LAST_UPDATED':
					return {
						...state,
						showLastUpdated: action.showLastUpdated,
					};
				case 'SET_BUTTON_TYPE':
					return {
						...state,
						buttonType: action.buttonType,
					};
				default:
					return state;
			}
		},
		actions,
		selectors: {
			getShowTopBar( state ) {
				return state.showTopBar;
			},
			getShowAuthorBar( state ) {
				return state.showAuthorBar;
			},
			getShowStatsBar( state ) {
				return state.showStatsBar;
			},
			getShowLastUpdated( state ) {
				return state.showLastUpdated;
			},
			getButtonType( state ) {
				return state.buttonType;
			},
		},
	} );
};
const blockStores = [];
const blockStore = ( uniqueId ) => {
	// Callers must skip select()/dispatch() when this is null. select(null) throws.
	if ( ! uniqueId ) {
		return null;
	}
	const storeName = `dlxplugins/github-grid-block/${ uniqueId }`;
	// Attempt to select the store to check if it's already registered
	const isStoreRegistered = select( storeName ); // can be undefined.

	if ( ! isStoreRegistered ) {
		const store = createBlockStore( uniqueId );

		// Make sure store is initialized. Check for instantiate function and return null if it doesn't exist.
		if ( ! store.instantiate ) {
			return storeName;
		}

		register( store );
		blockStores.push( store );
		return storeName;
	}

	// If the store is already registered, return its instance
	return storeName;
};
/**
 * Retrieve a current list of all registered blocks.
 *
 * @return {Array} Array of block stores
 */
const getBlockStores = () => {
	return blockStores;
};

export { blockStore, getBlockStores };
