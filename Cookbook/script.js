// -------- selectors --------
const searchForm  = document.querySelector('#search-form');
const searchInput = document.querySelector('#search');
const resultsList = document.querySelector('#results');

// -------- form handler --------
searchForm.addEventListener('submit', (e) => {
    e.preventDefault();
    searchRecipes();
});

// -------- main search function --------
async function searchRecipes() {
    const searchValue = searchInput.value.trim();
    if (!searchValue) {
        alert("Please enter at least one ingredient.");
        return;
    }

    // Split by comma, trim spaces, filter empty
    const ingredients = searchValue.split(',')
        .map(i => i.trim().toLowerCase())
        .filter(Boolean);

    if (ingredients.length === 0) {
        alert("Please enter valid ingredients.");
        return;
    }

    try {
        const response = await fetch('http://localhost:3001/search-recipes', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ingredients })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        displayRecipes(data);
    } catch (error) {
        console.error("Error fetching recipes:", error);
        alert("Failed to fetch recipes. Please try again.");
    }
}

// -------- improved renderer --------
function displayRecipes(recipes) {
    const results = document.querySelector('#results');

    if (!recipes || recipes.length === 0) {
        results.innerHTML = `
          <section class="empty">
            <div class="empty-box">
              <div class="emoji">🍳</div>
              <h3>No recipes found</h3>
              <p>Try adding or changing your ingredients.</p>
            </div>
          </section>`;
        return;
    }

    // Render chef-style cards
    const cards = recipes.map((r) => {
        // Normalize field names expected from your backend
        const title = r.name || r.title || 'Untitled Recipe';
        const cuisine = r.cuisine || '—';
        const time = r.cooking_time || r.time || '—';
        const instructions = (r.instructions || '').toString();
        const img = r.image || '';

        return `
          <article class="card">
            <div class="thumb" style="${img ? `background:center/cover no-repeat url('${img}')` : ''}">
              ${img ? '' : '🍲'}
            </div>
            <div class="body">
              <h3>${escapeHtml(title)}</h3>
              <div class="meta">
                <span>${escapeHtml(cuisine)}</span>
                <span class="dot"></span>
                <span>${escapeHtml(String(time))} min</span>
              </div>
              <div class="desc">${escapeHtml(instructions.slice(0, 200))}</div>
            </div>
            <div class="actions">
              <button class="btn save" data-title="${encodeURIComponent(title)}"
                      data-cuisine="${encodeURIComponent(cuisine)}"
                      data-time="${encodeURIComponent(time)}"
                      data-instructions="${encodeURIComponent(instructions)}">
                Save to My Cookbook
              </button>
              ${r.url 
                  ? `<a class="btn more" href="${r.url}" target="_blank" rel="noopener">Open Recipe</a>` 
                  : `<button class="btn more" onclick="alert('No external URL provided')">View / Edit</button>`}
            </div>
          </article>
        `;
    }).join('');

    results.innerHTML = `<section class="grid">${cards}</section>`;

    // Wire up Save buttons
    results.querySelectorAll('.btn.save').forEach(btn => {
        btn.addEventListener('click', async () => {
            const payload = {
                title: decodeURIComponent(btn.dataset.title || ''),
                cuisine: decodeURIComponent(btn.dataset.cuisine || ''),
                cooking_time: decodeURIComponent(btn.dataset.time || ''),
                instructions: decodeURIComponent(btn.dataset.instructions || '')
            };
            await saveRecipe(payload);
        });
    });
}

// -------- save to DB (via PHP) --------
async function saveRecipe(recipe) {
    try {
        const form = new FormData();
        form.append('title', recipe.title || '');
        form.append('cuisine', recipe.cuisine || '');
        form.append('cooking_time', recipe.cooking_time || '');
        form.append('instructions', recipe.instructions || '');

        const res = await fetch('add_recipe.php', { method: 'POST', body: form });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        alert('✅ Recipe saved to your cookbook!');
    } catch (e) {
        console.error(e);
        alert('❌ Could not save recipe (are you logged in, and is add_recipe.php correct?)');
    }
}

// -------- utility --------
function escapeHtml(str){
  return String(str).replace(/[&<>"']/g, s => (
    {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]
  ));
}
