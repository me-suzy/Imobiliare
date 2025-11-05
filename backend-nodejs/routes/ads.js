const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const { body, validationResult, query } = require('express-validator');
const Ad = require('../models/Ad');
const { authenticate, optionalAuth } = require('../middleware/auth');

// Configure multer for image uploads
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        cb(null, 'uploads/ads/');
    },
    filename: (req, file, cb) => {
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        cb(null, 'ad-' + uniqueSuffix + path.extname(file.filename));
    }
});

const upload = multer({
    storage: storage,
    limits: { fileSize: 5 * 1024 * 1024 }, // 5MB
    fileFilter: (req, file, cb) => {
        const allowedTypes = /jpeg|jpg|png|gif|webp/;
        const extname = allowedTypes.test(path.extname(file.originalname).toLowerCase());
        const mimetype = allowedTypes.test(file.mimetype);
        
        if (extname && mimetype) {
            return cb(null, true);
        }
        cb(new Error('Doar imagini sunt permise (JPG, PNG, GIF, WebP)'));
    }
});

// @route   GET /api/ads
// @desc    Get all ads with filters
// @access  Public
router.get('/', [
    query('page').optional().isInt({ min: 1 }),
    query('limit').optional().isInt({ min: 1, max: 100 }),
    query('category').optional().isString(),
    query('priceMin').optional().isNumeric(),
    query('priceMax').optional().isNumeric(),
    query('search').optional().isString()
], async (req, res) => {
    try {
        const page = parseInt(req.query.page) || 1;
        const limit = parseInt(req.query.limit) || 20;
        const skip = (page - 1) * limit;

        // Build query
        const query = { status: 'active' };
        
        if (req.query.category) query.category = req.query.category;
        if (req.query.priceMin || req.query.priceMax) {
            query['price.amount'] = {};
            if (req.query.priceMin) query['price.amount'].$gte = parseFloat(req.query.priceMin);
            if (req.query.priceMax) query['price.amount'].$lte = parseFloat(req.query.priceMax);
        }
        if (req.query.city) query['location.city'] = new RegExp(req.query.city, 'i');
        if (req.query.search) {
            query.$text = { $search: req.query.search };
        }

        // Execute query
        const ads = await Ad.find(query)
            .populate('seller', 'name rating avatar')
            .select('-__v')
            .sort(req.query.sort || '-createdAt')
            .skip(skip)
            .limit(limit);

        const total = await Ad.countDocuments(query);

        res.json({
            ads,
            pagination: {
                page,
                limit,
                total,
                pages: Math.ceil(total / limit)
            }
        });

    } catch (error) {
        console.error('Get ads error:', error);
        res.status(500).json({ error: 'Eroare la preluarea anunțurilor' });
    }
});

// @route   GET /api/ads/:id
// @desc    Get single ad
// @access  Public
router.get('/:id', optionalAuth, async (req, res) => {
    try {
        const ad = await Ad.findById(req.params.id)
            .populate('seller', 'name email phone rating avatar verified memberSince');

        if (!ad) {
            return res.status(404).json({ error: 'Anunț negăsit' });
        }

        // Increment views (only if not the owner)
        if (!req.userId || ad.seller._id.toString() !== req.userId.toString()) {
            await ad.incrementViews();
        }

        res.json({ ad });

    } catch (error) {
        console.error('Get ad error:', error);
        res.status(500).json({ error: 'Eroare la preluarea anunțului' });
    }
});

// @route   POST /api/ads
// @desc    Create new ad
// @access  Private
router.post('/', authenticate, upload.array('images', 10), [
    body('title').trim().isLength({ min: 10, max: 100 }),
    body('description').trim().isLength({ min: 20, max: 5000 }),
    body('category').isIn(['imobiliare', 'auto-moto', 'mobilier-casa', 'joburi', 'electronice', 'animale', 'fashion', 'sport', 'altele']),
    body('price').isNumeric({ min: 0 }),
    body('condition').isIn(['nou', 'folosit'])
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({ errors: errors.array() });
        }

        const { title, description, category, subcategory, price, currency, negotiable, condition, location, contact } = req.body;

        // Process uploaded images
        const images = req.files ? req.files.map(file => ({
            url: `/uploads/ads/${file.filename}`,
            filename: file.filename
        })) : [];

        // Create ad
        const ad = new Ad({
            title,
            description,
            category,
            subcategory,
            price: {
                amount: price,
                currency: currency || 'RON',
                negotiable: negotiable === 'true'
            },
            condition,
            images,
            location: JSON.parse(location),
            seller: req.userId,
            contact: JSON.parse(contact)
        });

        await ad.save();

        res.status(201).json({
            message: 'Anunț publicat cu succes!',
            ad
        });

    } catch (error) {
        console.error('Create ad error:', error);
        res.status(500).json({ error: 'Eroare la publicarea anunțului' });
    }
});

// @route   PUT /api/ads/:id
// @desc    Update ad
// @access  Private
router.put('/:id', authenticate, async (req, res) => {
    try {
        const ad = await Ad.findById(req.params.id);

        if (!ad) {
            return res.status(404).json({ error: 'Anunț negăsit' });
        }

        // Check ownership
        if (ad.seller.toString() !== req.userId.toString()) {
            return res.status(403).json({ error: 'Nu ai permisiunea să modifici acest anunț' });
        }

        // Update fields
        const { title, description, price, condition, status } = req.body;
        if (title) ad.title = title;
        if (description) ad.description = description;
        if (price) ad.price.amount = price;
        if (condition) ad.condition = condition;
        if (status) ad.status = status;

        await ad.save();

        res.json({
            message: 'Anunț actualizat cu succes!',
            ad
        });

    } catch (error) {
        console.error('Update ad error:', error);
        res.status(500).json({ error: 'Eroare la actualizarea anunțului' });
    }
});

// @route   DELETE /api/ads/:id
// @desc    Delete ad
// @access  Private
router.delete('/:id', authenticate, async (req, res) => {
    try {
        const ad = await Ad.findById(req.params.id);

        if (!ad) {
            return res.status(404).json({ error: 'Anunț negăsit' });
        }

        // Check ownership
        if (ad.seller.toString() !== req.userId.toString()) {
            return res.status(403).json({ error: 'Nu ai permisiunea să ștergi acest anunț' });
        }

        await ad.deleteOne();

        res.json({ message: 'Anunț șters cu succes!' });

    } catch (error) {
        console.error('Delete ad error:', error);
        res.status(500).json({ error: 'Eroare la ștergerea anunțului' });
    }
});

module.exports = router;


