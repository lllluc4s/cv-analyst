<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Oportunidade;
use Illuminate\Auth\Access\HandlesAuthorization;

class OportunidadePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Company $company): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Company $company, Oportunidade $oportunidade): bool
    {
        return $oportunidade->company_id === $company->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Company $company): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Company $company, Oportunidade $oportunidade): bool
    {
        return $oportunidade->company_id === $company->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Company $company, Oportunidade $oportunidade): bool
    {
        return $oportunidade->company_id === $company->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Company $company, Oportunidade $oportunidade): bool
    {
        return $oportunidade->company_id === $company->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Company $company, Oportunidade $oportunidade): bool
    {
        return $oportunidade->company_id === $company->id;
    }
}
