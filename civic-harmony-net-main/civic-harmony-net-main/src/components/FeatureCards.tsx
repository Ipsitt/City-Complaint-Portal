import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { 
  FileText, 
  MapPin, 
  Camera, 
  Search,
  TrendingUp,
  Shield
} from 'lucide-react';
import { Link } from 'react-router-dom';

const FeatureCards = () => {
  const features = [
    {
      icon: FileText,
      title: 'Report Issues',
      description: 'Submit complaints about roads, infrastructure, safety hazards, and community problems with detailed descriptions.',
      color: 'text-primary',
      bgColor: 'bg-primary/10',
      link: '/report'
    },
    {
      icon: Camera,
      title: 'Photo Evidence',
      description: 'Upload photos to provide visual evidence and help authorities understand the severity of the issue.',
      color: 'text-secondary',
      bgColor: 'bg-secondary/10',
      link: '/report'
    },
    {
      icon: MapPin,
      title: 'Location Tracking',
      description: 'Precise location data ensures authorities can quickly locate and address the reported problems.',
      color: 'text-primary',
      bgColor: 'bg-primary/10',
      link: '/report'
    },
    {
      icon: Search,
      title: 'Track Progress',
      description: 'Monitor the status of your complaints and see real-time updates on resolution progress.',
      color: 'text-secondary',
      bgColor: 'bg-secondary/10',
      link: '/track'
    },
    {
      icon: TrendingUp,
      title: 'Community Impact',
      description: 'View community statistics and see how collective reporting leads to positive changes.',
      color: 'text-primary',
      bgColor: 'bg-primary/10',
      link: '/about'
    },
    {
      icon: Shield,
      title: 'Secure & Anonymous',
      description: 'Your reports are secure and you can choose to report anonymously while maintaining accountability.',
      color: 'text-secondary',
      bgColor: 'bg-secondary/10',
      link: '/about'
    }
  ];

  return (
    <section className="py-20 bg-muted/30">
      <div className="container mx-auto px-4">
        <div className="text-center mb-16">
          <h2 className="text-3xl md:text-4xl font-bold text-foreground mb-4">
            How It Works
          </h2>
          <p className="text-xl text-muted-foreground max-w-2xl mx-auto">
            Simple, effective tools to help you make a difference in your community
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {features.map((feature, index) => (
            <Card key={index} className="group hover:shadow-lg transition-all duration-300 border-0 shadow-md">
              <CardHeader className="pb-4">
                <div className={`w-12 h-12 ${feature.bgColor} rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform`}>
                  <feature.icon className={`w-6 h-6 ${feature.color}`} />
                </div>
                <CardTitle className="text-xl font-semibold">{feature.title}</CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-muted-foreground text-base leading-relaxed mb-4">
                  {feature.description}
                </CardDescription>
                <Link to={feature.link}>
                  <Button variant="ghost" size="sm" className="text-primary hover:text-primary-hover p-0">
                    Learn more →
                  </Button>
                </Link>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </section>
  );
};

export default FeatureCards;