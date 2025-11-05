const jwt = require('jsonwebtoken');
const User = require('../models/User');

// Middleware pentru verificarea token-ului JWT
const authenticate = async (req, res, next) => {
    try {
        // Get token from header
        const token = req.header('Authorization')?.replace('Bearer ', '');
        
        if (!token) {
            return res.status(401).json({ 
                error: 'Acces interzis. Token lipsă.' 
            });
        }

        // Verify token
        const decoded = jwt.verify(token, process.env.JWT_SECRET);
        
        // Find user
        const user = await User.findById(decoded.userId);
        
        if (!user || !user.active) {
            return res.status(401).json({ 
                error: 'Token invalid sau utilizator inactiv.' 
            });
        }

        // Attach user to request
        req.user = user;
        req.userId = user._id;
        
        next();
    } catch (error) {
        if (error.name === 'JsonWebTokenError') {
            return res.status(401).json({ error: 'Token invalid.' });
        }
        if (error.name === 'TokenExpiredError') {
            return res.status(401).json({ error: 'Token expirat. Te rugăm să te autentifici din nou.' });
        }
        res.status(500).json({ error: 'Eroare la autentificare.' });
    }
};

// Optional authentication (pentru endpoints care funcționează și fără autentificare)
const optionalAuth = async (req, res, next) => {
    try {
        const token = req.header('Authorization')?.replace('Bearer ', '');
        
        if (token) {
            const decoded = jwt.verify(token, process.env.JWT_SECRET);
            const user = await User.findById(decoded.userId);
            
            if (user && user.active) {
                req.user = user;
                req.userId = user._id;
            }
        }
        
        next();
    } catch (error) {
        // Continuăm fără autentificare dacă token-ul e invalid
        next();
    }
};

module.exports = { authenticate, optionalAuth };


