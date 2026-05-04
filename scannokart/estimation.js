require('dotenv').config();

const express = require('express');
const cors = require('cors');
const { GoogleGenerativeAI } = require("@google/generative-ai");

const app = express();
app.use(cors({ origin: 'https://scannokart.org', methods: ['POST', 'OPTIONS'], allowedHeaders: ['Content-Type'] })); // Allows Server to talk to HTML
app.use(express.json()); // Allows the server to read JSON sent from your HTML

// --- INVOKING THE SYSTEM VARIABLE ---
// 'process.env' is how Node.js looks at your computer's system variables.
const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);
const model = genAI.getGenerativeModel({ 
model: "gemini-2.5-flash-lite", requestOptions: { timeout: 30000 } });

app.post('/api/get-expiration', async (req, res) => {
    try {
        const pantryItems = req.body.items; // This is the array from your cookie
        if (!pantryItems || !Array.isArray(pantryItems)) {
		return res.status(400).send("No items provided")
	}

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

app.post('/api/process-receipt', async (req, res) => {
    try {
        const rawText = req.body.raw_text;
        
        const prompt = `
            Task: Convert the following raw OCR text from a grocery receipt into a clean JSON array.
            
            DATA: ${rawText}

            INSTRUCTIONS:
            1. Identify food items and quantities.
            2. Return ONLY a valid JSON array of objects.
            3. Each object MUST have these keys: "name", "qty", and "type" (category).
            4. Do NOT include markdown code blocks, introductory text, or explanations.
	    5. Try your best to correct any misspellings obscurations.
	    6. If there are duplicate items just increase the quantity for the first recorded item.
            
            Example Format: [{"name": "Milk", "qty": 1, "type": "Dairy"}]
        `;

        const result = await model.generateContent(prompt);
        let responseText = result.response.text();
        
        // CLEANUP: Remove markdown ```json blocks and any text before/after the brackets
        const jsonMatch = responseText.match(/\[.*\]/s);
        if (!jsonMatch) {
            throw new Error("AI did not return a valid JSON array");
        }
        
        const cleanJson = jsonMatch[0];
        res.json(JSON.parse(cleanJson));

    } catch (error) {
        console.error("Gemini Error:", error);
        res.status(500).send("Gemini processing failed");
    }
});

app.listen(4000,'0.0.0.0', () => console.log('Server running on port 4000'));
