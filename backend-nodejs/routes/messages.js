const express = require('express');
const router = express.Router();
const { Message, Conversation } = require('../models/Message');
const { authenticate } = require('../middleware/auth');

// @route   GET /api/messages/conversations
// @desc    Get all conversations for user
// @access  Private
router.get('/conversations', authenticate, async (req, res) => {
    try {
        const conversations = await Conversation.find({
            participants: req.userId
        })
        .populate('participants', 'name avatar')
        .populate('ad', 'title images')
        .populate('lastMessage')
        .sort('-lastMessageAt');

        res.json({ conversations });
    } catch (error) {
        console.error('Get conversations error:', error);
        res.status(500).json({ error: 'Eroare la preluarea conversațiilor' });
    }
});

// @route   GET /api/messages/:conversationId
// @desc    Get messages in a conversation
// @access  Private
router.get('/:conversationId', authenticate, async (req, res) => {
    try {
        const conversation = await Conversation.findById(req.params.conversationId);
        
        if (!conversation || !conversation.participants.includes(req.userId)) {
            return res.status(403).json({ error: 'Acces interzis' });
        }

        const messages = await Message.find({ conversation: req.params.conversationId })
            .populate('sender', 'name avatar')
            .sort('createdAt');

        // Mark messages as read
        await Message.updateMany(
            { conversation: req.params.conversationId, receiver: req.userId, read: false },
            { read: true, readAt: new Date() }
        );

        res.json({ messages });
    } catch (error) {
        console.error('Get messages error:', error);
        res.status(500).json({ error: 'Eroare la preluarea mesajelor' });
    }
});

// @route   POST /api/messages
// @desc    Send a message
// @access  Private
router.post('/', authenticate, async (req, res) => {
    try {
        const { receiverId, adId, content, conversationId } = req.body;

        let conversation;

        if (conversationId) {
            conversation = await Conversation.findById(conversationId);
        } else {
            // Find or create conversation
            conversation = await Conversation.findOne({
                participants: { $all: [req.userId, receiverId] },
                ad: adId
            });

            if (!conversation) {
                conversation = new Conversation({
                    participants: [req.userId, receiverId],
                    ad: adId
                });
                await conversation.save();
            }
        }

        // Create message
        const message = new Message({
            conversation: conversation._id,
            sender: req.userId,
            receiver: receiverId,
            ad: adId,
            content
        });

        await message.save();

        // Update conversation
        conversation.lastMessage = message._id;
        conversation.lastMessageAt = new Date();
        await conversation.save();

        await message.populate('sender', 'name avatar');

        res.status(201).json({ message, conversationId: conversation._id });
    } catch (error) {
        console.error('Send message error:', error);
        res.status(500).json({ error: 'Eroare la trimiterea mesajului' });
    }
});

module.exports = router;


