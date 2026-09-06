<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartyRequest;
use App\Models\Party;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * All Customer and Supplier CRUD. The concrete children declare only which
 * role they are; every method below is shared and the wording comes from
 * config/parties.php.
 */
abstract class BasePartyController extends Controller
{
    /** 'customer' or 'supplier'. */
    abstract protected function role(): string;

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $parties = Party::query()
            ->where($this->flag(), true)
            ->when($q !== '', fn ($query) => $query->where(fn ($sub) => $sub
                ->where('name', 'ilike', "%{$q}%")
                ->orWhere('email', 'ilike', "%{$q}%")
                ->orWhere('phone', 'ilike', "%{$q}%")))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('parties.index', $this->withRoles(['parties' => $parties, 'q' => $q]));
    }

    public function create(): View
    {
        $party = new Party(['is_active' => true, $this->flag() => true]);

        return view('parties.create', $this->withRoles(['party' => $party]));
    }

    public function store(StorePartyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data[$this->flag()] = true;

        $party = Party::create($data);

        return redirect()
            ->route($this->config('route').'.index')
            ->with('status', $this->config('singular').' "'.$party->name.'" created.');
    }

    public function edit(Party $party): View
    {
        abort_unless($party->{$this->flag()}, 404);

        return view('parties.edit', $this->withRoles(['party' => $party]));
    }

    public function update(StorePartyRequest $request, Party $party): RedirectResponse
    {
        abort_unless($party->{$this->flag()}, 404);

        $data = $request->validated();
        $data[$this->flag()] = true;

        $party->update($data);

        return redirect()
            ->route($this->config('route').'.index')
            ->with('status', $this->config('singular').' "'.$party->name.'" updated.');
    }

    public function destroy(Party $party): RedirectResponse
    {
        abort_unless($party->{$this->flag()}, 404);

        if ($party->documents()->exists() || $party->payments()->exists()) {
            return back()->with('error', $this->config('singular').' "'.$party->name.'" has documents or payments and cannot be deleted.');
        }

        $party->delete();

        return redirect()
            ->route($this->config('route').'.index')
            ->with('status', $this->config('singular').' deleted.');
    }

    private function flag(): string
    {
        return $this->config('flag');
    }

    private function config(string $key): string
    {
        return config("parties.roles.{$this->role()}.{$key}");
    }

    /**
     * Add this role and the other role to the view data, for the shared views.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withRoles(array $data): array
    {
        $other = $this->role() === 'customer' ? 'supplier' : 'customer';

        return array_merge($data, [
            'role' => config("parties.roles.{$this->role()}"),
            'otherRole' => config("parties.roles.{$other}"),
        ]);
    }
}
