require('dotenv').config();

const express = require('express');
const cors = require('cors');
const { GoogleGenerativeAI } = require("@google/generative-ai");

const app = express();
app.use(cors({ origin: '*' })); // Allows Server to talk to HTML
app.use(express.json()); // Allows the server to read JSON sent from your HTML

// --- INVOKING THE SYSTEM VARIABLE ---
// 'process.env' is how Node.js looks at your computer's system variables.
const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);
const model = genAI.getGenerativeModel({ 
model: "gemini-2.5-flash", requestOptions: { timeout: 30000 } });

app.post('/get-expiration', async (req, res) => {
    try {
        const pantryItems = req.body.items; // This is the array from your cookie
        
        // Convert your array of objects into a simple string for the AI
        const itemString = pantryItems.map(i => `${i.qty} ${i.name}`).join(", ");

        const prompt = `Analyze this pantry list: ${itemString}. 
        Return a JSON array of objects with "name" and "daysToExpiration" (as a number).
        Only return the JSON code, no extra text.`;

        const result = await model.generateContent(prompt);
        const responseText = result.response.text();
        
        // Clean the response (sometimes AI adds ```json blocks) and parse it
        const cleanJson = responseText.replace(/```json|```/g, "").trim();
        res.json(JSON.parse(cleanJson));

    } catch (error) {
        console.error(error);
        res.status(500).send("Error communicating with Gemini");
    }
});

app.listen(3000,'0.0.0.0', () => console.log('Server running on http://localhost:3000'));
