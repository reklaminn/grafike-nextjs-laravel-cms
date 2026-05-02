<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('language')
            ->withCount('items')
            ->orderBy('name')
            ->get();

        return view('admin.menus.index', compact('menus'));
    }

    public function create()
    {
        $languages = Language::where('is_active', true)->get();
        return view('admin.menus.create', compact('languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:50',
            'language_id' => ['required', Rule::exists('central.languages', 'id')],
        ]);

        $menu = Menu::create([
            'name' => $request->name,
            'slug' => $this->makeUniqueSlug($request->name),
            'location' => $request->location,
            'language_id' => $request->language_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.menus.edit', $menu)
            ->with('success', 'Menü başarıyla oluşturuldu.');
    }

    public function edit(Menu $menu)
    {
        $menu->load(['items' => function ($q) {
            $q->whereNull('parent_id')->orderBy('sort_order')->with('children');
        }, 'language']);

        $languages = Language::where('is_active', true)->get();
        $pages = Page::where('status', 'published')->orderBy('title')->get(['id', 'title', 'slug']);

        return view('admin.menus.edit', compact('menu', 'languages', 'pages'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:50',
            'language_id' => ['required', Rule::exists('central.languages', 'id')],
        ]);

        $menu->update([
            'name' => $request->name,
            'location' => $request->location,
            'language_id' => $request->language_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.menus.edit', $menu)
            ->with('success', 'Menü başarıyla güncellendi.');
    }

    public function destroy(Menu $menu)
    {
        $menu->items()->delete();
        $menu->delete();

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Menü başarıyla silindi.');
    }

    /**
     * Add a new menu item via AJAX.
     */
    public function addItem(Request $request, Menu $menu)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:500',
            'page_id' => 'nullable|exists:pages,id',
            'parent_id' => 'nullable|exists:menu_items,id',
            'target' => 'nullable|in:_self,_blank',
        ]);

        $maxOrder = $menu->items()->max('sort_order') ?? 0;

        $item = $menu->items()->create([
            'title' => $request->title,
            'url' => $request->url ?? '#',
            'page_id' => $request->page_id,
            'parent_id' => $request->parent_id,
            'target' => $request->target ?? '_self',
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'item' => $item]);
    }

    /**
     * Update menu item order via AJAX (drag & drop).
     */
    public function reorderItems(Request $request, Menu $menu)
    {
        $request->validate(['items' => 'required|array']);

        $this->updateItemOrder($request->items);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a menu item via AJAX.
     */
    public function deleteItem(Menu $menu, MenuItem $item)
    {
        // Reparent children to the item's parent
        $item->children()->update(['parent_id' => $item->parent_id]);
        $item->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Recursively update menu item order.
     */
    protected function updateItemOrder(array $items, ?int $parentId = null): void
    {
        foreach ($items as $index => $item) {
            MenuItem::where('id', $item['id'])->update([
                'sort_order' => $index,
                'parent_id' => $parentId,
            ]);

            if (!empty($item['children'])) {
                $this->updateItemOrder($item['children'], $item['id']);
            }
        }
    }

    protected function makeUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'menu';
        $slug = $baseSlug;
        $counter = 2;

        while (Menu::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
