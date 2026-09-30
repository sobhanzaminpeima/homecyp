<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DashboardMenuController extends Controller
{
    public function store(Request $request)
    {
        $business = $this->ownedBusiness($request);

        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);

        $menu = $business->menus()->create($data);

        return new MenuResource($menu->load('items'));
    }

    public function update(Request $request, Menu $menu)
    {
        $this->authorizeMenu($request, $menu);

        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);
        $menu->update($data);

        return new MenuResource($menu->load('items'));
    }

    public function destroy(Request $request, Menu $menu)
    {
        $this->authorizeMenu($request, $menu);
        $menu->delete();

        return response()->json(['message' => 'Menu deleted.']);
    }

    public function storeItem(Request $request, Menu $menu)
    {
        $this->authorizeMenu($request, $menu);

        $data = $this->validatedItem($request);
        $item = $menu->items()->create($data);

        return response()->json($item);
    }

    public function updateItem(Request $request, MenuItem $item)
    {
        $this->authorizeMenu($request, $item->menu);

        $data = $this->validatedItem($request);
        $item->update($data);

        return response()->json($item);
    }

    public function destroyItem(Request $request, MenuItem $item)
    {
        $this->authorizeMenu($request, $item->menu);
        $item->delete();

        return response()->json(['message' => 'Item deleted.']);
    }

    private function validatedItem(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
            'is_available' => ['boolean'],
        ]);
    }

    private function ownedBusiness(Request $request)
    {
        $business = $request->user()->businesses()->first();

        if (! $business) {
            throw ValidationException::withMessages(['business' => ['You do not have a business listing yet.']]);
        }

        return $business;
    }

    private function authorizeMenu(Request $request, Menu $menu): void
    {
        if ($menu->business->owner_id !== $request->user()->id) {
            abort(403);
        }
    }
}
