const express = require('express');
const router = express.Router();
const User = require('../models/User');
const Ad = require('../models/Ad');
const { authenticate } = require('../middleware/auth');

// @route   GET /api/users/:id
// @desc    Get user profile
// @access  Public
router.get('/:id', async (req, res) => {
    try {
        const user = await User.findById(req.params.id);
        
        if (!user) {
            return res.status(404).json({ error: 'Utilizator negăsit' });
        }

        res.json({ user: user.toPublicJSON() });
    } catch (error) {
        console.error('Get user error:', error);
        res.status(500).json({ error: 'Eroare la preluarea utilizatorului' });
    }
});

// @route   GET /api/users/:id/ads
// @desc    Get user's ads
// @access  Public
router.get('/:id/ads', async (req, res) => {
    try {
        const ads = await Ad.find({
            seller: req.params.id,
            status: 'active'
        })
        .select('-__v')
        .sort('-createdAt');

        res.json({ ads, count: ads.length });
    } catch (error) {
        console.error('Get user ads error:', error);
        res.status(500).json({ error: 'Eroare la preluarea anunțurilor' });
    }
});

module.exports = router;


