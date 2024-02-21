<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Company;
use App\Events\CompanyChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class CompaniesAccounts
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class CompaniesAccounts extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        //Paginate companies by 10
        if ($request->get('domain') != null) {
            $limit = 100;
        } else {
            $limit = $request->get('entries') ?? 10;
        }
        $companies = Company::when($request->get('domain') != null, function ($q) use ($request) {
            return $q->where('domain', 'like', $request->get('domain'));
        }, function ($q) {
            return Company::orderBy('created_at', 'asc');
        })->paginate($limit)->withQueryString();

        return view('pages.company.companies')->with('companies', $companies);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        // Get Company by id
        $company = Company::find($id);

        return view('pages.company.company')->with('company', $company);
    }

    /**
     * @param  Request  $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Get Company by id
        $company = Company::find($id);
        // Get company data
        $company_data = $request->input();

        // Update only one field
        $company->update(['company_status' => intval($company_data['company_status'])]);

        if ($company->company_status === Company::APPROVED_COMPANY_STATUS) {
            event(new CompanyChanged($company, 'approvedByAdmin'));
        }

        // Return message
        session()->flash('message', 'Company status is changed.');

        return redirect()->route('companies');
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function store(Request $request)
    {
        // Get company data
        $company_data = $request->input();

        // Get company domain
        $companyDomain = $company_data['domain'];

        // Check if company exists by domain
        $existsDomain = DB::table('companies')
            ->where('domain', $companyDomain)
            ->first();

        if ($existsDomain) {
            // Return message
            $request->session()->flash('message-failed', 'Company with this domain already exist.');

            return view('pages.company.company');
        }

        // Set default value
        $company_status = 2;

        // Check if Company is approved
        if (in_array('company_status', $company_data)) {
            $company_status = intval($company_data['company_status']);
        }

        // Create new Company
        $company = new Company();
        $company->company_name = $company_data['company_name'];
        $company->domain = $company_data['domain'];
        $company->company_address = $company_data['company_address'];
        $company->company_city = $company_data['company_city'];
        $company->company_state = $company_data['company_state'];
        $company->company_zip_code = $company_data['company_zip_code'];
        $company->company_phone = $company_data['company_phone'];
        $company->company_status = $company_status;
        $company->save();

        // Return message
        $request->session()->flash('message', 'You added a new Company');

        return view('pages.company.company')->with('company', $company);
    }

    /**
     * @param  Request  $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, $id)
    {
        // Get company data from request
        $company_data = $request->input();

        // Get Company by id
        $company = Company::find($id);

        // Set default value
        $company_status = 2;

        // Check if Company is approved
        if (array_key_exists('company_status', $company_data)) {
            $company_status = intval($company_data['company_status']);
        }

        // Update Company
        $company->update([
            'company_name' => $company_data['company_name'],
            'domain' => $company_data['domain'],
            'company_address' => $company_data['company_address'],
            'company_city' => $company_data['company_city'],
            'company_state' => $company_data['company_state'],
            'company_zip_code' => $company_data['company_zip_code'],
            'company_phone' => $company_data['company_phone'],
            'company_status' => $company_status,
        ]);

        // Return message
        session()->flash('message', 'Company information has been updated');

        return view('pages.company.company')->with('company', $company);
    }

    /**
     * Sync company status from is_approved to company status field
     */
    public function syncCompanyStatus(): string
    {
        $allCompanies = Company::all();

        foreach ($allCompanies as $company) {
            if ($company->is_approved) {
                $company->update(['company_status' => Company::APPROVED_COMPANY_STATUS]);
            } elseif (! $company->is_approved) {
                $company->update(['company_status' => Company::PENDING_COMPANY_STATUS]);
            }
        }

        return 'Update';
    }
}
