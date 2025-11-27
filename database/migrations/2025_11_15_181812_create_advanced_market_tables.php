<?php
// database/migrations/2024_01_01_create_advanced_market_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Enhanced Market Regimes Table
        Schema::create('market_regimes', function (Blueprint $table) {
            $table->id();
            $table->dateTime('timestamp');
            $table->date('date');
            $table->string('symbol', 20);
            $table->decimal('price', 18, 8);
            $table->decimal('volume', 25, 8);
            $table->decimal('market_cap', 25, 2)->nullable();
            
            // Technical Indicators
            $table->decimal('volatility_24h', 10, 6);
            $table->decimal('rsi_14', 5, 2)->nullable();
            $table->decimal('macd', 10, 6)->nullable();
            $table->decimal('bollinger_upper', 18, 8)->nullable();
            $table->decimal('bollinger_lower', 18, 8)->nullable();
            
            // Regime Classification
            $table->enum('regime', ['bull', 'bear', 'neutral', 'volatile', 'reversal']);
            $table->decimal('regime_confidence', 5, 4);
            $table->json('regime_metadata')->nullable();
            
            // Dominance & Sentiment
            $table->decimal('dominance_score', 5, 2)->nullable();
            $table->decimal('sentiment_score', 5, 2)->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['date', 'symbol']);
            $table->index(['timestamp', 'regime']);
            $table->index('dominance_score');
            $table->index('regime_confidence');
        });

        // Regime Summaries Table
        Schema::create('regime_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('time')->nullable();
            
            // Market Metrics
            $table->integer('total_symbols');
            $table->json('regime_distribution');
            $table->decimal('market_health_score', 5, 2);
            $table->decimal('volatility_index', 5, 2);
            
            // Sentiment Analysis
            $table->enum('market_sentiment', [
                'extremely_bullish', 'bullish', 'neutral', 'bearish', 'extremely_bearish'
            ]);
            $table->decimal('sentiment_score', 5, 2);
            $table->json('sentiment_indicators')->nullable();
            
            // Trends
            $table->decimal('trend_strength', 5, 2);
            $table->decimal('reversal_probability', 5, 4)->nullable();
            $table->string('next_regime_prediction')->nullable();
            $table->decimal('prediction_confidence', 5, 4)->nullable();
            
            // Dominance
            $table->json('top_dominance');
            $table->json('dominance_trends')->nullable();
            
            $table->timestamps();
            
            $table->index(['date', 'market_sentiment']);
            $table->index('market_health_score');
        });

        // Dominance History Table
        Schema::create('dominance_history', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('symbol', 20);
            $table->decimal('dominance_score', 5, 2);
            $table->integer('rank');
            $table->decimal('market_cap', 25, 2);
            $table->decimal('volume_24h', 25, 2);
            $table->decimal('price_change_7d', 10, 6)->nullable();
            $table->integer('rank_change')->default(0);
            
            $table->timestamps();
            
            $table->index(['date', 'symbol']);
            $table->index(['date', 'rank']);
        });

        // Market Events Table
        Schema::create('market_events', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('symbol', 20)->nullable();
            $table->enum('event_type', ['regime_change', 'dominance_shift', 'volatility_spike', 'volume_surge', 'market_insight']);
            $table->string('title');
            $table->text('description');
            $table->json('previous_state')->nullable();
            $table->json('current_state')->nullable();
            $table->enum('severity', ['info', 'warning', 'critical']);
            $table->boolean('is_read')->default(false);
            $table->timestamp('triggered_at');
            
            $table->timestamps();
            
            $table->index(['date', 'event_type']);
            $table->index('severity');
        });

        // Market Patterns Table (Optional - for future use)
        Schema::create('market_patterns', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('symbol', 20)->nullable();
            $table->string('pattern_type');
            $table->string('pattern_name');
            $table->json('pattern_data')->nullable();
            $table->decimal('confidence', 5, 4);
            $table->enum('direction', ['bullish', 'bearish', 'neutral']);
            $table->decimal('price_target', 18, 8)->nullable();
            $table->dateTime('expiry')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_triggered')->default(false);
            
            $table->timestamps();
            
            $table->index(['date', 'pattern_type']);
            $table->index(['symbol', 'is_active']);
        });

        // Market Alerts Table
        Schema::create('market_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_type');
            $table->string('symbol', 20)->nullable();
            $table->string('title');
            $table->text('message');
            $table->json('trigger_conditions')->nullable();
            $table->json('market_data')->nullable();
            $table->enum('severity', ['info', 'warning', 'critical']);
            $table->boolean('is_read')->default(false);
            $table->timestamp('triggered_at');
            
            $table->timestamps();
            
            $table->index(['triggered_at', 'severity']);
            $table->index(['alert_type', 'is_read']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('market_alerts');
        Schema::dropIfExists('market_patterns');
        Schema::dropIfExists('market_events');
        Schema::dropIfExists('dominance_history');
        Schema::dropIfExists('regime_summaries');
        Schema::dropIfExists('market_regimes');
    }
};