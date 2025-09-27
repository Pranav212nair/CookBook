const express = require('express');
const cors = require('cors');
const fs = require('fs');
const path = require('path');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');

const app = express();
const PORT = 3001;
const JWT_SECRET = 'supersecretkey'; // In production, use env variable

app.use(cors());
app.use(express.json());

// Load recipes from JSON file
const recipesPath = path.join(__dirname, 'recipes.json');
function getRecipes() {
    const data = fs.readFileSync(recipesPath, 'utf-8');
    return JSON.parse(data);
}

const usersPath = path.join(__dirname, 'users.json');

// Helper to read users
function getUsers() {
    const data = fs.readFileSync(usersPath, 'utf-8');
    return JSON.parse(data);
}

// Helper to write users
function saveUsers(users) {
    fs.writeFileSync(usersPath, JSON.stringify(users, null, 2));
}

// Middleware to verify JWT
function authenticateToken(req, res, next) {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];
    if (!token) return res.sendStatus(401);
    jwt.verify(token, JWT_SECRET, (err, user) => {
        if (err) return res.sendStatus(403);
        req.user = user;
        next();
    });
}

// POST /search-recipes
// Expects: { ingredients: ["ingredient1", "ingredient2", ...] }
app.post('/search-recipes', (req, res) => {
    const userIngredients = req.body.ingredients || [];
    if (!Array.isArray(userIngredients) || userIngredients.length === 0) {
        return res.status(400).json({ error: 'No ingredients provided.' });
    }

    const recipes = getRecipes();
    // Find recipes where all required ingredients are in user's list
    const matches = recipes.filter(recipe =>
        recipe.ingredients.every(ing => userIngredients.includes(ing))
    );
	res.json(matches);
});

// POST /register
// Expects: { username, password }
app.post('/register', async (req, res) => {
    const { username, password } = req.body;
    if (!username || !password) {
        return res.status(400).json({ error: 'Username and password required.' });
    }
    let users = getUsers();
    if (users.find(u => u.username === username)) {
        return res.status(409).json({ error: 'Username already exists.' });
    }
    const hashed = await bcrypt.hash(password, 10);
    const newUser = {
        id: users.length ? users[users.length - 1].id + 1 : 1,
        username,
        password: hashed,
        favorites: [],
        mealplan: []
    };
    users.push(newUser);
    saveUsers(users);
    res.json({ success: true });
});

// POST /login
// Expects: { username, password }
app.post('/login', async (req, res) => {
    const { username, password } = req.body;
    if (!username || !password) {
        return res.status(400).json({ error: 'Username and password required.' });
    }
    const users = getUsers();
    const user = users.find(u => u.username === username);
    if (!user) {
        return res.status(401).json({ error: 'Invalid username or password.' });
    }
    const match = await bcrypt.compare(password, user.password);
    if (!match) {
        return res.status(401).json({ error: 'Invalid username or password.' });
    }
    // Generate JWT
    const token = jwt.sign({ id: user.id, username: user.username }, JWT_SECRET, { expiresIn: '2h' });
    res.json({ token, username: user.username });
});

// --- FAVORITES ---
// GET /favorites (get user's favorites)
app.get('/favorites', authenticateToken, (req, res) => {
    const users = getUsers();
    const user = users.find(u => u.id === req.user.id);
    if (!user) return res.sendStatus(404);
    res.json({ favorites: user.favorites });
});

// POST /favorites (add a recipe to favorites)
// Expects: { recipeId }
app.post('/favorites', authenticateToken, (req, res) => {
    const { recipeId } = req.body;
    if (typeof recipeId !== 'number') return res.status(400).json({ error: 'recipeId required.' });
    let users = getUsers();
    const user = users.find(u => u.id === req.user.id);
    if (!user) return res.sendStatus(404);
    if (!user.favorites.includes(recipeId)) user.favorites.push(recipeId);
    saveUsers(users);
    res.json({ favorites: user.favorites });
});

// DELETE /favorites (remove a recipe from favorites)
// Expects: { recipeId }
app.delete('/favorites', authenticateToken, (req, res) => {
    const { recipeId } = req.body;
    if (typeof recipeId !== 'number') return res.status(400).json({ error: 'recipeId required.' });
    let users = getUsers();
    const user = users.find(u => u.id === req.user.id);
    if (!user) return res.sendStatus(404);
    user.favorites = user.favorites.filter(id => id !== recipeId);
    saveUsers(users);
    res.json({ favorites: user.favorites });
});

// --- MEAL PLAN ---
// GET /mealplan (get user's meal plan)
app.get('/mealplan', authenticateToken, (req, res) => {
    const users = getUsers();
    const user = users.find(u => u.id === req.user.id);
    if (!user) return res.sendStatus(404);
    res.json({ mealplan: user.mealplan });
});

// POST /mealplan (add a recipe to meal plan)
// Expects: { recipeId }
app.post('/mealplan', authenticateToken, (req, res) => {
    const { recipeId } = req.body;
    if (typeof recipeId !== 'number') return res.status(400).json({ error: 'recipeId required.' });
    let users = getUsers();
    const user = users.find(u => u.id === req.user.id);
    if (!user) return res.sendStatus(404);
    if (!user.mealplan.includes(recipeId)) user.mealplan.push(recipeId);
    saveUsers(users);
    res.json({ mealplan: user.mealplan });
});

// DELETE /mealplan (remove a recipe from meal plan)
// Expects: { recipeId }
app.delete('/mealplan', authenticateToken, (req, res) => {
    const { recipeId } = req.body;
    if (typeof recipeId !== 'number') return res.status(400).json({ error: 'recipeId required.' });
    let users = getUsers();
    const user = users.find(u => u.id === req.user.id);
    if (!user) return res.sendStatus(404);
    user.mealplan = user.mealplan.filter(id => id !== recipeId);
    saveUsers(users);
    res.json({ mealplan: user.mealplan });
});

app.listen(PORT, () => {
    console.log(`Recipe backend running on http://localhost:${PORT}`);
});