import { Button } from '@/components/ui/button';
import { ArrowRight, Users, Target, Globe } from 'lucide-react';
import { Link } from 'react-router-dom';
import heroImage from '@/assets/hero-community.jpg';

const Hero = () => {
  return (
    <section className="relative min-h-[600px] flex items-center overflow-hidden">
      {/* Background Image */}
      <div 
        className="absolute inset-0 bg-cover bg-center bg-no-repeat"
        style={{ backgroundImage: `url(${heroImage})` }}
      >
        <div className="absolute inset-0 bg-gradient-to-r from-primary/90 via-primary/70 to-secondary/80"></div>
      </div>

      {/* Content */}
      <div className="relative container mx-auto px-4 py-20">
        <div className="max-w-4xl">
          <h1 className="text-4xl md:text-6xl font-bold text-primary-foreground mb-6 leading-tight">
            Building Better Communities
            <span className="block text-3xl md:text-5xl text-primary-foreground/90 font-medium mt-2">
              One Report at a Time
            </span>
          </h1>
          
          <p className="text-xl md:text-2xl text-primary-foreground/90 mb-8 max-w-2xl leading-relaxed">
            Help local authorities address urban problems faster. Report infrastructure issues, 
            safety hazards, and community concerns to create safer, cleaner, and more sustainable cities.
          </p>

          {/* CTA Buttons */}
          <div className="flex flex-col sm:flex-row gap-4 mb-12">
            <Link to="/report">
              <Button variant="secondary" size="lg" className="text-lg px-8 py-3">
                Report an Issue
                <ArrowRight className="w-5 h-5 ml-2" />
              </Button>
            </Link>
            <Link to="/track">
              <Button variant="outline" size="lg" className="text-lg px-8 py-3 bg-background/10 backdrop-blur-sm border-primary-foreground/20 text-primary-foreground hover:bg-background/20">
                Track Your Complaint
              </Button>
            </Link>
          </div>

          {/* Stats/Impact */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 pt-8 border-t border-primary-foreground/20">
            <div className="flex items-center space-x-3">
              <div className="w-12 h-12 bg-secondary/20 rounded-full flex items-center justify-center">
                <Users className="w-6 h-6 text-primary-foreground" />
              </div>
              <div>
                <div className="text-2xl font-bold text-primary-foreground">10,000+</div>
                <div className="text-primary-foreground/80">Active Citizens</div>
              </div>
            </div>
            
            <div className="flex items-center space-x-3">
              <div className="w-12 h-12 bg-secondary/20 rounded-full flex items-center justify-center">
                <Target className="w-6 h-6 text-primary-foreground" />
              </div>
              <div>
                <div className="text-2xl font-bold text-primary-foreground">85%</div>
                <div className="text-primary-foreground/80">Issues Resolved</div>
              </div>
            </div>
            
            <div className="flex items-center space-x-3">
              <div className="w-12 h-12 bg-secondary/20 rounded-full flex items-center justify-center">
                <Globe className="w-6 h-6 text-primary-foreground" />
              </div>
              <div>
                <div className="text-2xl font-bold text-primary-foreground">UN SDGs</div>
                <div className="text-primary-foreground/80">Aligned Goals</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Hero;