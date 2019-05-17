<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $caps = DB::select(
			"
                SELECT
                    facilities.`code` AS facility_code,
                    facilities.`name` AS facility_name,
                    facilities.city AS facility_city,
                    states.`name` AS state_name,
                    facilities.zip AS facility_zip,
                    inspections.cap_due_date,
                    inspections.code AS inspection_code,
                    users.`name` AS inspector
                FROM
                    inspections
                INNER JOIN facilities ON facilities.id = inspections.facility_id
                INNER JOIN users ON users.id = inspections.updatedbyuser_id
                INNER JOIN states ON states.id = facilities.state_id
                WHERE
                    inspections.active = '1'
                AND inspections.deleted = '0'
                AND inspections.`status` = 'COMPLETED'
                AND inspections.cap_due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)
            ");

        $dataset_1 = DB::select(
            "
                SELECT
                    (
                        SELECT
                            COUNT(vendors.id)
                        FROM
                            vendors
                        WHERE
                            deleted = '0'
                    ) AS vendors,
                    (
                        SELECT
                            COUNT(facilities.id)
                        FROM
                            facilities
                        WHERE
                            deleted = '0'
                    ) AS facilities,
                    (
                        SELECT
                            COUNT(apartments.id)
                        FROM
                            apartments
                        WHERE
                            deleted = '0'
                    ) AS apartments,
                    (
                        SELECT
                            COUNT(clients.id)
                        FROM
                            clients
                        WHERE
                            deleted = '0'
                    ) AS clients,
                    (
                        SELECT
                            COUNT(clients.id)
                        FROM
                            clients
                        WHERE
                            deleted = '0'
                        AND active = '1'
                    ) AS clients_active,
                    (
                        SELECT
                            COUNT(clients.id)
                        FROM
                            clients
                        WHERE
                            deleted = '0'
                        AND active = '0'
                    ) AS clients_inactive,
                    (
                        SELECT
                            COUNT(apartments.id)
                        FROM
                            apartments
                        WHERE
                            deleted = '0'
                        AND free = '1'
                        AND active = '1'
                    ) AS vacant,
                    (
                        SELECT
                            COUNT(apartments.id)
                        FROM
                            apartments
                        WHERE
                            deleted = '0'
                        AND free = '0'
                    ) AS occupied,
                    (
                        SELECT
                            COUNT(vendors.id)
                        FROM
                            vendors
                        WHERE
                            deleted = '0'
                        AND active = '1'
                    ) AS vendors_active,
                    (
                        SELECT
                            COUNT(vendors.id)
                        FROM
                            vendors
                        WHERE
                            deleted = '0'
                        AND active = '0'
                    ) AS vendors_inactive,
                    (
                        SELECT
                            COUNT(facilities.id)
                        FROM
                            facilities
                        WHERE
                            deleted = '0'
                        AND active = '1'
                    ) AS facilities_active,
                    (
                        SELECT
                            COUNT(facilities.id)
                        FROM
                            facilities
                        WHERE
                            deleted = '0'
                        AND active = '0'
                    ) AS facilities_non_referral,
                    (
                        SELECT
                            COUNT(apartments.id)
                        FROM
                            apartments
                        WHERE
                            deleted = '0'
                        AND active = '1'
                    ) AS apartments_online,
                    (
                        SELECT
                            COUNT(apartments.id)
                        FROM
                            apartments
                        WHERE
                            deleted = '0'
                        AND active = '0'
                    ) AS apartments_offline,
					(
                        SELECT
                            COUNT(floors.id) as total
                        FROM
                            floors
						INNER JOIN facilities ON facilities.id=floors.facility_id
						INNER JOIN apartments ON apartments.floor_id=floors.id
                        WHERE
                            facilities.type = 'SINGLE'
						AND apartments.deleted='0'
						AND apartments.active='1'
                    ) AS apts_single,
                    (
                         SELECT
                            COUNT(floors.id) as total
                        FROM
                            floors
						INNER JOIN facilities ON facilities.id=floors.facility_id
						INNER JOIN apartments ON apartments.floor_id=floors.id
                        WHERE
                            facilities.type = 'FAMILY'
						AND apartments.deleted='0'
						AND apartments.active='1'
                    ) AS apts_family,
					(
                        SELECT
                            COUNT(floors.id) as total
                        FROM
                            floors
						INNER JOIN facilities ON facilities.id=floors.facility_id
						INNER JOIN apartments ON apartments.floor_id=floors.id
                        WHERE
                            facilities.type = 'SINGLE'
						AND apartments.deleted='0'
						AND apartments.active='1'
						AND apartments.free='0'
                    ) AS apts_single_occupied,
                    (
                         SELECT
                            COUNT(floors.id) as total
                        FROM
                            floors
						INNER JOIN facilities ON facilities.id=floors.facility_id
						INNER JOIN apartments ON apartments.floor_id=floors.id
                        WHERE
                            facilities.type = 'SINGLE'
						AND apartments.deleted='0'
						AND apartments.active='1'
						AND apartments.free='1'
                    ) AS apts_single_vacant,
					(
                        SELECT
                            COUNT(floors.id) as total
                        FROM
                            floors
						INNER JOIN facilities ON facilities.id=floors.facility_id
						INNER JOIN apartments ON apartments.floor_id=floors.id
                        WHERE
                            facilities.type = 'FAMILY'
						AND apartments.deleted='0'
						AND apartments.active='1'
						AND apartments.free='0'
                    ) AS apts_family_occupied,
                    (
                         SELECT
                            COUNT(floors.id) as total
                        FROM
                            floors
						INNER JOIN facilities ON facilities.id=floors.facility_id
						INNER JOIN apartments ON apartments.floor_id=floors.id
                        WHERE
                            facilities.type = 'FAMILY'
						AND apartments.deleted='0'
						AND apartments.active='1'
						AND apartments.free='1'
                    ) AS apts_family_vacant,
		    (
			SELECT
			COUNT(facilities.id) AS SinFac
			FROM facilities
			WHERE type='single'
			AND active='1'
			AND deleted='0'
			) AS facilities_single_online,
			(
			SELECT
			COUNT(facilities.id) AS FamFac
			FROM facilities
			WHERE type='family'
			AND active='1'
			AND deleted='0'
			) AS facilities_family_online,
			(
			SELECT
			COUNT(clients.id) AS admitted
			FROM clients
			INNER JOIN preconditions ON preconditions.id=clients.precondition_id
			WHERE
				preconditions.id in (6,5)
			AND 	clients.active='1'
			AND 	clients.deleted='0'

			) AS clients_admitted,
			(
			SELECT
			COUNT(clients.id) AS discharged
			FROM clients
			INNER JOIN preconditions ON preconditions.id=clients.precondition_id
			WHERE
				preconditions.id='3'
			AND 	clients.active='1'
			AND 	clients.deleted='0'

			) AS clients_discharged,
			(
			SELECT
			COUNT(clients.id) AS NoShow
			FROM clients
			INNER JOIN preconditions ON preconditions.id=clients.precondition_id
			WHERE
				preconditions.id='14'
			AND 	clients.active='1'
			AND 	clients.deleted='0'

			) AS clients_noshow,
			(
			SELECT
			COUNT(clients.id) AS NoShow
			FROM clients
			INNER JOIN preconditions ON preconditions.id=clients.precondition_id
			WHERE
				preconditions.id='1'
			AND 	clients.active='1'
			AND 	clients.deleted='0'
			) AS clients_referral,
			(
			SELECT
			sum(facilities.no_of_units) AS total_c_f_units
			FROM
				facilities
		    WHERE active='1'
			AND city='Bronx'
			) AS bronx_apts,
			(
			SELECT
			sum(facilities.no_of_units) AS total_c_f_units
			FROM
				facilities
		    WHERE active='1'
			AND city='Brooklyn'
			) AS brooklyn_apts,
			(
			SELECT
			sum(facilities.no_of_units) AS total_c_f_units
			FROM
				facilities
		    WHERE active='1'
			AND city='New York'
			) AS ny_apts,
			(
			SELECT
			sum(facilities.no_of_units) AS total_c_f_units
			FROM
				facilities
		    WHERE active='1'
			AND city='Queens'
			) AS queens_apts,
			(
			SELECT
			COALESCE(SUM(facilities.no_of_units),0) AS total_c_f_units
			FROM
				facilities
		    WHERE active='1'
            AND type='FAMILY'
			AND city='Queens'
			) AS queens_apts_family,
			(
			SELECT
			COALESCE(SUM(facilities.no_of_units),0) AS total_c_f_units
			FROM
				facilities
		    WHERE active='1'
            AND type='SINGLE'
			AND city='Queens'
			) AS queens_apts_single,
			(
			SELECT
			COALESCE(SUM(facilities.no_of_units),0) AS total_c_f_units
			FROM
				facilities
		    WHERE active='1'
            AND type='FAMILY'
			AND city='Bronx'
			) AS bronx_apts_family,
			(
			SELECT
			COALESCE(SUM(facilities.no_of_units),0) AS total_c_f_units
			FROM
				facilities
		    WHERE active='1'
            AND type='SINGLE'
			AND city='Bronx'
			) AS bronx_apts_single,
			(
			SELECT
			COALESCE(SUM(facilities.no_of_units),0) AS total_c_f_units
			FROM
				facilities
		    WHERE active='1'
            AND type='FAMILY'
			AND city='Brooklyn'
			) AS brooklyn_apts_family,
			(
			SELECT
			COALESCE(SUM(facilities.no_of_units),0) AS total_c_f_units
			FROM
				facilities
		    WHERE active='1'
            AND type='SINGLE'
			AND city='Brooklyn'
			) AS brooklyn_apts_single,
			(
			SELECT
			COALESCE(SUM(facilities.no_of_units),0) AS total_c_f_units
			FROM
				facilities
		    WHERE active='1'
            AND type='FAMILY'
			AND city='New York'
			) AS ny_apts_family,
			(
			SELECT
			COALESCE(SUM(facilities.no_of_units),0) AS total_c_f_units
			FROM
				facilities
		    WHERE active='1'
            AND type='SINGLE'
			AND city='New York'
			) AS ny_apts_single,
			(
			SELECT
			COUNT(facilities.vendor_id) AS total_c_facilities
			FROM
				facilities
		    WHERE active='1'
            AND type='FAMILY'
			AND city='Queens'
			) AS queens_fac_family,
			(
			SELECT
			COUNT(facilities.vendor_id) AS total_c_facilities
			FROM
				facilities
		    WHERE active='1'
            AND type='SINGLE'
			AND city='Queens'
			) AS queens_fac_single,
			(
			SELECT
			COUNT(facilities.vendor_id) AS total_c_facilities
			FROM
				facilities
		    WHERE active='1'
            AND type='FAMILY'
			AND city='Bronx'
			) AS bronx_fac_family,
			(
			SELECT
			COUNT(facilities.vendor_id) AS total_c_facilities
			FROM
				facilities
		    WHERE active='1'
            AND type='SINGLE'
			AND city='Bronx'
			) AS bronx_fac_single,
			(
			SELECT
			COUNT(facilities.vendor_id) AS total_c_facilities
			FROM
				facilities
		    WHERE active='1'
            AND type='FAMILY'
			AND city='Brooklyn'
			) AS brooklyn_fac_family,
			(
			SELECT
			COUNT(facilities.vendor_id) AS total_c_facilities
			FROM
				facilities
		    WHERE active='1'
            AND type='SINGLE'
			AND city='Brooklyn'
			) AS brooklyn_fac_single,
			(
			SELECT
			COUNT(facilities.vendor_id) AS total_c_facilities
			FROM
				facilities
		    WHERE active='1'
            AND type='FAMILY'
			AND city='New York'
			) AS ny_fac_family,
			(
			SELECT
			COUNT(facilities.vendor_id) AS total_c_facilities
			FROM
				facilities
		    WHERE active='1'
            AND type='SINGLE'
			AND city='New York'
			) AS ny_fac_single,
			(
			SELECT
				SUM(facilities.no_of_units) AS total_units
			FROM
				facilities
		    WHERE facilities.active='1'
			) AS total_apts_contract

            "
        );

        $barChart = $this->barChart();
		$dataset_3 = $this->dataset_3();
        return view('home',compact('caps','dataset_3','dataset_1','barChart'));
    }

    public function _404() {
        return view('pages.404');
    }

    public function _500() {
        return view('pages.500');
    }


    public function barChart() {
        $barChart = DB::select(
            "
               SELECT
    MONTHNAME(
        DATE_FORMAT(
            LAST_DAY(NOW() - INTERVAL 1 MONTH),
            '%Y-%m-%d 23:59:59'
        )
    ) AS Previous_month,
    MONTHNAME(
        DATE_FORMAT(
            LAST_DAY(NOW() - INTERVAL 2 MONTH),
            '%Y-%m-%d 23:59:59'
        )
    ) AS Previous_month_1,
    MONTHNAME(
        DATE_FORMAT(
            LAST_DAY(NOW() - INTERVAL 3 MONTH),
            '%Y-%m-%d 23:59:59'
        )
    ) AS Previous_month_2,
    MONTHNAME(
        DATE_FORMAT(
            LAST_DAY(NOW() - INTERVAL 4 MONTH),
            '%Y-%m-%d 23:59:59'
        )
    ) AS Previous_month_3,
    MONTHNAME(
        DATE_FORMAT(
            LAST_DAY(NOW() - INTERVAL 5 MONTH),
            '%Y-%m-%d 23:59:59'
        )
    ) AS Previous_month_4,
    MONTHNAME(
        DATE_FORMAT(
            LAST_DAY(NOW() - INTERVAL 6 MONTH),
            '%Y-%m-%d 23:59:59'
        )
    ) AS Previous_month_5
UNION ALL
    SELECT
        (
            SELECT
                count(apartments.id)
            FROM
                apartments
            WHERE
                apartments.created_at <= DATE_FORMAT(
                    LAST_DAY(NOW() - INTERVAL 1 MONTH),
                    '%Y-%m-%d 23:59:59'
                )
            AND active = '1'
            AND deleted = '0'
        ) AS Previous_month,
        (
            SELECT
                count(apartments.id)
            FROM
                apartments
            WHERE
                apartments.created_at <= DATE_FORMAT(
                    LAST_DAY(NOW() - INTERVAL 2 MONTH),
                    '%Y-%m-%d 23:59:59'
                )
            AND active = '1'
            AND deleted = '0'
        ) AS Previous_month_1,
        (
            SELECT
                count(apartments.id)
            FROM
                apartments
            WHERE
                apartments.created_at <= DATE_FORMAT(
                    LAST_DAY(NOW() - INTERVAL 3 MONTH),
                    '%Y-%m-%d 23:59:59'
                )
            AND active = '1'
            AND deleted = '0'
        ) AS Previous_month_2,
        (
            SELECT
                count(apartments.id)
            FROM
                apartments
            WHERE
                apartments.created_at <= DATE_FORMAT(
                    LAST_DAY(NOW() - INTERVAL 4 MONTH),
                    '%Y-%m-%d 23:59:59'
                )
            AND active = '1'
            AND deleted = '0'
        ) AS Previous_month_3,
        (
            SELECT
                count(apartments.id)
            FROM
                apartments
            WHERE
                apartments.created_at <= DATE_FORMAT(
                    LAST_DAY(NOW() - INTERVAL 5 MONTH),
                    '%Y-%m-%d 23:59:59'
                )
            AND active = '1'
            AND deleted = '0'
        ) AS Previous_month_4,
        (
            SELECT
                count(apartments.id)
            FROM
                apartments
            WHERE
                apartments.created_at <= DATE_FORMAT(
                    LAST_DAY(NOW() - INTERVAL 6 MONTH),
                    '%Y-%m-%d 23:59:59'
                )
            AND active = '1'
            AND deleted = '0'
        )
    UNION ALL
        SELECT
            (
                SELECT
                    COUNT(DISTINCT(apartment_id)) AS T
                FROM
                    apartmentallotments
                WHERE
                    vacatedon BETWEEN DATE_FORMAT(
                        LAST_DAY(NOW() - INTERVAL 1 MONTH),
                        '%Y-%m-01 00:00:00'
                    )
                AND DATE_FORMAT(
                    LAST_DAY(NOW() - INTERVAL 1 MONTH),
                    '%Y-%m-%d 23:59:59'
                )
                AND active = '1'
                AND deleted = '0'
            ) AS Previous_month,
            (
                SELECT
                    COUNT(DISTINCT(apartment_id)) AS T
                FROM
                    apartmentallotments
                WHERE
                    vacatedon BETWEEN DATE_FORMAT(
                        LAST_DAY(NOW() - INTERVAL 2 MONTH),
                        '%Y-%m-01 00:00:00'
                    )
                AND DATE_FORMAT(
                    LAST_DAY(NOW() - INTERVAL 2 MONTH),
                    '%Y-%m-%d 23:59:59'
                )
                AND active = '1'
                AND deleted = '0'
            ) AS Previous_month_1,
            (
                SELECT
                    COUNT(DISTINCT(apartment_id)) AS T
                FROM
                    apartmentallotments
                WHERE
                    vacatedon BETWEEN DATE_FORMAT(
                        LAST_DAY(NOW() - INTERVAL 3 MONTH),
                        '%Y-%m-01 00:00:00'
                    )
                AND DATE_FORMAT(
                    LAST_DAY(NOW() - INTERVAL 3 MONTH),
                    '%Y-%m-%d 23:59:59'
                )
                AND active = '1'
                AND deleted = '0'
            ) AS Previous_month_2,
            (
                SELECT
                    COUNT(DISTINCT(apartment_id)) AS T
                FROM
                    apartmentallotments
                WHERE
                    vacatedon BETWEEN DATE_FORMAT(
                        LAST_DAY(NOW() - INTERVAL 4 MONTH),
                        '%Y-%m-01 00:00:00'
                    )
                AND DATE_FORMAT(
                    LAST_DAY(NOW() - INTERVAL 4 MONTH),
                    '%Y-%m-%d 23:59:59'
                )
                AND active = '1'
                AND deleted = '0'
            ) AS Previous_month_3,
            (
                SELECT
                    COUNT(DISTINCT(apartment_id)) AS T
                FROM
                    apartmentallotments
                WHERE
                    vacatedon BETWEEN DATE_FORMAT(
                        LAST_DAY(NOW() - INTERVAL 5 MONTH),
                        '%Y-%m-01 00:00:00'
                    )
                AND DATE_FORMAT(
                    LAST_DAY(NOW() - INTERVAL 5 MONTH),
                    '%Y-%m-%d 23:59:59'
                )
                AND active = '1'
                AND deleted = '0'
            ) AS Previous_month_4,
            (
                SELECT
                    COUNT(DISTINCT(apartment_id)) AS T
                FROM
                    apartmentallotments
                WHERE
                    vacatedon BETWEEN DATE_FORMAT(
                        LAST_DAY(NOW() - INTERVAL 6 MONTH),
                        '%Y-%m-01 00:00:00'
                    )
                AND DATE_FORMAT(
                    LAST_DAY(NOW() - INTERVAL 6 MONTH),
                    '%Y-%m-%d 23:59:59'
                )
                AND active = '1'
                AND deleted = '0'
            ) AS Previous_month_5
            "
        );

        return $barChart;

    }
	
    public function dataset_3() {
	$dataset_3 = DB::select(
		"
		    SELECT
				facilities.city as cities,
                		states.name as states,
				count(facilities.vendor_id) AS total_c_facilities,
				SUM(facilities.no_of_units) AS total_c_f_units
		
			FROM
				facilities
			INNER JOIN states ON states.id=facilities.state_id
            
		    WHERE facilities.active='1'
		    GROUP BY facilities.city, states.name
            ");

        return $dataset_3;

    }

    public function sess_out() {
        Auth::logout();
        return redirect()->route('login');
    }

}
