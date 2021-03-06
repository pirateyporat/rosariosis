<?php

if ( ! $_REQUEST['search_modfunc'])
{
	switch (User('PROFILE'))
	{
		case 'admin':
		case 'teacher':
			//if (UserStaffID() && ($_REQUEST['modname']!='Users/Search.php' || $_REQUEST['student_id']=='new'))
			if (UserStaffID() && User('PROFILE')=='admin' && $_REQUEST['staff_id']=='new')
				unset($_SESSION['staff_id']);

			$_SESSION['Search_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars'],array('bottom_back','advanced'));
			if ( $_SESSION['Back_PHP_SELF']!='staff')
			{
				$_SESSION['Back_PHP_SELF'] = 'staff';
				unset($_SESSION['List_PHP_SELF']);
			}

			echo '<br />';

			PopTable('header',$extra['search_title']?$extra['search_title']:_('Find a User'));

			echo '<form name="search" id="search" action="Modules.php?modname='.$_REQUEST['modname'].'&modfunc='.$_REQUEST['modfunc'].'&search_modfunc=list&next_modname='.$_REQUEST['next_modname'].'&advanced='.$_REQUEST['advanced'].$extra['action'].'" method="GET">';

			echo '<table class="width-100p col1-align-right" id="general_table">';

			echo '<tr><td><label for="last">' . _( 'Last Name' ) . '</label></td>
				<td><input type="text" name="last" id="last" size="30" autofocus /></td></tr>';

			echo '<tr><td><label for="first">' . _( 'First Name' ) . '</label></td>
				<td><input type="text" name="first" id="first" size="30" /></td></tr>';

			echo '<tr><td><label for="usrid">' . _( 'User ID' ) . '</label></td>
				<td><input type="text" name="usrid" id="usrid" size="30" /></td></tr>';

			echo '<tr><td><label for="username">' . _( 'Username' ) . '</label></td>
				<td><input type="text" name="username" id="username" size="30" /></td></tr>';

			if ( User( 'PROFILE' ) == 'admin' )
			{
				$options = array(
					'' => _( 'N/A' ),
					'admin' => _( 'Administrator' ),
					'teacher' => _( 'Teacher' ),
					'parent' => _( 'Parent' ),
					'none' => _( 'No Access' ),
				);
			}
			else
			{
				$options = array(
					'' => _( 'N/A' ),
					'teacher' => _( 'Teacher' ),
					'parent' => _( 'Parent' ),
				);
			}

			if ( $extra['profile'] )
			{
				$options = array( $extra['profile'] => $options[ $extra['profile'] ] );
			}

			echo '<tr><td><label for="profile">' . _( 'Profile' ) . '</label></td>
				<td><select name="profile" id="profile">';

			foreach ( (array) $options as $key => $val )
			{
				echo '<option value="' . $key . '">' . $val . '</option>';
			}

			echo '</select></td></tr>';

			if ( ! isset( $extra ) )
			{
				$extra = array();
			}

			StaffWidgets( 'user', $extra );

			Search(
				'staff_fields',
				is_array( $extra['staff_fields'] ) ? $extra['staff_fields'] : array()
			);

			echo '</table><div class="center">';

			if ( $extra['search_second_col'] )
				echo $extra['search_second_col'];

			if ( User('PROFILE') === 'admin' )
			{
				// FJ if only one school, no Search All Schools option.
				// Restrict Search All Schools to user schools.
				if ( SchoolInfo( 'SCHOOLS_NB' ) > 1
					&& ( ! trim( User( 'SCHOOLS' ), ',' )
						|| mb_substr_count( User( 'SCHOOLS' ), ',' ) > 2 ) )
				{
					echo '<label><input type="checkbox" name="_search_all_schools" value="Y"' .
						( Preferences( 'DEFAULT_ALL_SCHOOLS' ) == 'Y' ? ' checked' : '' ) . '>&nbsp;' .
						_( 'Search All Schools' ) . '</label><br />';
				}
			}

			echo '<label><input type="checkbox" name="include_inactive" value="Y" /> ' .
				_( 'Include Parents of Inactive Students' ) . '</label><br />';

			echo '<br />' . Buttons( _( 'Submit' ), _( 'Reset' ) ) . '<br /><br /></div>';

			if ( $extra['search']
				|| $extra['extra_search']
				|| $extra['second_col'] )
			{
				echo '<table class="widefat width-100p col1-align-right">';

				if ( $extra['search'] )
					echo $extra['search'];

				if ( $extra['extra_search'] )
					echo $extra['extra_search'];

				if ( $extra['second_col'] )
					echo $extra['second_col'];

				echo '</table>';
			}

			//echo '<table><tr class="valign-top"><td>';

			if ( $_REQUEST['advanced']=='Y')
			{
				$extra['search'] = '';

				StaffWidgets( 'all', $extra );

				if ( $extra['search'] )
				{
					echo PopTable( 'header', _( 'Widgets' ) );

					echo $extra['search'];

					echo PopTable( 'footer' ) . '<br />';
				}

				ob_start();

				Search(
					'staff_fields_all',
					is_array( $extra['staff_fields'] ) ? $extra['staff_fields'] : array()
				);

				$staff_fields_all = ob_get_clean();

				if ( $staff_fields_all )
				{
					echo PopTable( 'header', _( 'User Fields' ) );

					echo $staff_fields_all;

					echo PopTable( 'footer' ) . '<br />';
				}

				echo '<a href="'.PreparePHP_SELF($_REQUEST,array(),array('advanced' => 'N')).'">'._('Basic Search').'</a>';
			}
			else
				echo '<br /><a href="'.PreparePHP_SELF($_REQUEST,array(),array('advanced' => 'Y')).'">'._('Advanced Search').'</a>';

			echo '</form>';

			// update Bottom.php
			echo '<script>ajaxLink("Bottom.php"); old_modname="";</script>';

			PopTable( 'footer' );

		break;

		default:

			echo User( 'PROFILE' );
	}
}
//if ( $_REQUEST['search_modfunc']=='list')
else
{
	if ( ! $_REQUEST['next_modname'])
		$_REQUEST['next_modname'] = 'Users/User.php';

	if ( !isset($extra))
		$extra = array();

	if ( ! $extra['NoSearchTerms'])
	{
		if ( $_REQUEST['_search_all_schools']=='Y')
			$_ROSARIO['SearchTerms'] .= '<b>'._('Search All Schools').'</b><br />';
	}

	if ( !isset($_ROSARIO['DrawHeader']))
		DrawHeader(_('Choose A User'));

	$staff_RET = GetStaffList($extra);

	if ( $extra['profile'] )
	{
		// DO NOT translate those strings since they will be passed to ListOutput ultimately.
		$options = array(
			'admin' => 'Administrator',
			'teacher' => 'Teacher',
			'parent' => 'Parent',
			'none' => 'No Access',
		);

		$options_plural = array(
			'admin' => 'Administrators',
			'teacher' => 'Teachers',
			'parent' => 'Parents',
			'none' => 'No Access',
		);

		$singular = $options[ $extra['profile'] ];

		$plural = $options_plural[ $extra['profile'] ];

		$columns = array(
			'FULL_NAME' => $singular,
			'STAFF_ID' => sprintf( _( '%s ID' ), Config( 'NAME' ) ),
		);
	}
	else
	{
		$singular = 'User';

		$plural = 'Users';

		$columns = array(
			'FULL_NAME' => _( 'User' ),
			'PROFILE' => _( 'Profile' ),
			'STAFF_ID' => sprintf( _( '%s ID' ), Config( 'NAME' ) ),
		);
	}

	$name_link['FULL_NAME']['link'] = 'Modules.php?modname='.$_REQUEST['next_modname'];
	$name_link['FULL_NAME']['variables'] = array('staff_id' => 'STAFF_ID');

	if (isset($extra['link']) && is_array($extra['link']))
		$link = $extra['link'] + $name_link;
	else
		$link = $name_link;

	if (isset($extra['columns_before']) && is_array($extra['columns_before']))
		$columns = $extra['columns_before'] + $columns;

	if (isset($extra['columns_after']) && is_array($extra['columns_after']))
		$columns += $extra['columns_after'];

	if (count($staff_RET)>1 || $link['add'] || ! $link['FULL_NAME'] || $extra['columns_before'] || $extra['columns_after'] || ($extra['BackPrompt']==false && count($staff_RET)==0) || ($extra['Redirect']===false && count($staff_RET)==1))
	{
		if ( $_REQUEST['expanded_view']!='true')
			DrawHeader('<a href="'.PreparePHP_SELF($_REQUEST,array(),array('expanded_view' => 'true')) . '">'._('Expanded View').'</a>',$extra['header_right']);
		else
			DrawHeader('<a href="'.PreparePHP_SELF($_REQUEST,array(),array('expanded_view' => 'false')) . '">'._('Original View').'</a>',$extra['header_right']);

		if ( $extra['extra_header_left']
			|| $extra['extra_header_right'] )
		{
			DrawHeader( $extra['extra_header_left'], $extra['extra_header_right'] );
		}

		DrawHeader( mb_substr($_ROSARIO['SearchTerms'], 0, -6 ) );

		if ( ! $_REQUEST['LO_save'] && ! $extra['suppress_save'])
		{
			$_SESSION['List_PHP_SELF'] = PreparePHP_SELF($_SESSION['_REQUEST_vars'],array('bottom_back'));

			if ( $_SESSION['Back_PHP_SELF']!='staff')
			{
				$_SESSION['Back_PHP_SELF'] = 'staff';
				unset($_SESSION['Search_PHP_SELF']);
			}

			echo '<script>ajaxLink("Bottom.php"); old_modname="";</script>';
		}

		ListOutput( $staff_RET, $columns, $singular, $plural, $link, false, $extra['options'] );
	}
	elseif (count($staff_RET)==1)
	{
		if (count($link['FULL_NAME']['variables']))
		{
			foreach ( (array) $link['FULL_NAME']['variables'] as $var => $val)
				$_REQUEST[ $var ] = $staff_RET['1'][ $val ];
		}

		if ( !is_array($staff_RET[1]['STAFF_ID']))
		{
			SetUserStaffID($staff_RET[1]['STAFF_ID']);

			unset($_REQUEST['search_modfunc']);
		}

		if ( $_REQUEST['modname']!=$_REQUEST['next_modname'])
		{
			$modname = $_REQUEST['next_modname'];

			if (mb_strpos($modname,'?'))
				$modname = mb_substr($_REQUEST['next_modname'],0,mb_strpos($_REQUEST['next_modname'],'?'));

			if (mb_strpos($modname,'&'))
				$modname = mb_substr($_REQUEST['next_modname'],0,mb_strpos($_REQUEST['next_modname'],'&'));

			if ( $_REQUEST['modname'])
				$_REQUEST['modname'] = $modname;

			//FJ security fix, cf http://www.securiteam.com/securitynews/6S02U1P6BI.html
			if (mb_substr($modname, -4, 4)!='.php' || mb_strpos($modname, '..')!==false || !is_file('modules/'.$modname))
			{
				require_once 'ProgramFunctions/HackingLog.fnc.php';
				HackingLog();
			}
			else
				require_once 'modules/'.$modname;
		}
	}
	else
	{
		DrawHeader('',$extra['header_right']);

		if ( $extra['extra_header_left']
			|| $extra['extra_header_right'] )
		{
			DrawHeader( $extra['extra_header_left'], $extra['extra_header_right'] );
		}

		DrawHeader( mb_substr( $_ROSARIO['SearchTerms'], 0, -6 ) );

		echo ErrorMessage(array(_('No Users were found.')));
	}
}
