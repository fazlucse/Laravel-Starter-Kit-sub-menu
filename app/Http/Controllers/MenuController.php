<?php
namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Tymon\JWTAuth\Facades\JWTAuth;
class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('children')->orderBy('order')->get();
        return Inertia::render('menu/index');
    }

    public function create()
    {
        $parents = Menu::orderBy('name')->get();
        return Inertia::render('menu/create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'route' => 'nullable|string',
            'icon' => 'nullable|string',
            'order' => 'nullable|integer',
            'parent_id' => 'nullable|exists:menus,id',
        ]);

        Menu::create($request->all());

        return redirect()->route('menus.index')->with('success', 'Menu created successfully');
    }

    public function edit(Menu $menu)
    {
        $parents = Menu::where('id', '!=', $menu->id)->orderBy('name')->get();
        return Inertia::render('Menu/Edit', compact('menu', 'parents'));
    }
    public function show( $id){

    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'name' => 'required|string',
            'route' => 'nullable|string',
            'icon' => 'nullable|string',
            'order' => 'nullable|integer',
            'parent_id' => 'nullable|exists:menus,id',
        ]);

        $menu->update($request->all());

        return redirect()->route('menus.index')->with('success', 'Menu updated successfully');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->route('menus.index')->with('success', 'Menu deleted successfully');
    }
}
